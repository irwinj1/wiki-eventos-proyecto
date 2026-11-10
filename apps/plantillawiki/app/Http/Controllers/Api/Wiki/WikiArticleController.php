<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Wiki;

use App\Http\Controllers\Controller;
use App\Models\WikiArticle;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WikiArticleController extends Controller
{
    /**
     * Listar artículos y tutoriales de la Wiki con filtros por tipo, categoría, etiqueta, publicación y búsqueda.
     */
    public function index(Request $request)
    {
        try {
            $query = WikiArticle::with([
                'category:id,name,slug',
                'author:id,name,email,role',
                'tags:id,name,slug',
            ])->where('estado', true);

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->integer('category_id'));
            }

            if ($request->filled('category_slug')) {
                $slug = $request->input('category_slug');
                $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
            }

            if ($request->filled('tag_slug')) {
                $tagSlug = $request->input('tag_slug');
                $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
            }

            if ($request->filled('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }

            if ($request->filled('search')) {
                $like = '%' . strtolower($request->input('search')) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(content) LIKE ?', [$like]);
                });
            }

            $articles = $query->orderByDesc('created_at')->paginate(12);

            $pagination = [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ];

            return ApiResponse::success('Listado de artículos de la Wiki', 200, $articles->items(), $pagination);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los artículos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear artículo o tutorial con generación automática de slug único y asociación de tags.
     */
    public function store(Request $request)
    {
        $baseSlug = Str::slug($request->input('title', ''));
        $slug = $this->resolveUniqueSlug($baseSlug);

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:wiki_categories,id'],
            'type' => ['sometimes', 'string', Rule::in(['tutorial', 'article', 'Tutorial', 'Artículo'])],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:wiki_tags,id'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $authorId = auth('api')->id() ?? $request->input('author_id', 1);

            $article = WikiArticle::create([
                'category_id' => $request->category_id,
                'author_id' => $authorId,
                'type' => $request->input('type', 'article'),
                'title' => $request->title,
                'slug' => $slug,
                'summary' => $request->input('summary') ?: Str::limit(strip_tags($request->content), 180),
                'content' => $request->content,
                'is_published' => $request->boolean('is_published', false),
                'estado' => true,
            ]);

            if ($request->filled('tags')) {
                $article->tags()->sync($request->input('tags'));
            }

            DB::commit();

            return ApiResponse::success(
                'Artículo creado correctamente',
                201,
                $article->load(['category:id,name,slug', 'author:id,name', 'tags:id,name,slug'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al crear el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar un artículo por ID o por slug.
     */
    public function show(string $idOrSlug)
    {
        $article = WikiArticle::with([
            'category:id,name,slug',
            'author:id,name,email,role',
            'tags:id,name,slug',
        ])
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->first();

        if (!$article) {
            return ApiResponse::error('Artículo no encontrado', 404);
        }

        return ApiResponse::success('Artículo encontrado', 200, $article);
    }

    /**
     * Actualizar artículo/tutorial, publicar/despublicar y sincronizar etiquetas.
     */
    public function update(Request $request, string $id)
    {
        $article = WikiArticle::find($id);
        if (!$article) {
            return ApiResponse::error('Artículo no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:wiki_categories,id'],
            'type' => ['sometimes', 'string', Rule::in(['tutorial', 'article', 'Tutorial', 'Artículo'])],
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['sometimes', 'required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:wiki_tags,id'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $payload = $request->only(['category_id', 'type', 'title', 'summary', 'content', 'is_published']);

            if ($request->filled('title') && $request->title !== $article->title) {
                $payload['slug'] = $this->resolveUniqueSlug(Str::slug($request->title), $article->id);
            }

            $article->update($payload);

            if ($request->has('tags') && is_array($request->tags)) {
                $article->tags()->sync($request->tags);
            }

            DB::commit();

            return ApiResponse::success(
                'Artículo actualizado correctamente',
                200,
                $article->load(['category:id,name,slug', 'author:id,name', 'tags:id,name,slug'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al actualizar el artículo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo del artículo.
     */
    public function destroy(string $id)
    {
        $article = WikiArticle::find($id);
        if (!$article) {
            return ApiResponse::error('Artículo no encontrado', 404);
        }

        $article->estado = !$article->estado;
        $article->save();

        return ApiResponse::success('Estado del artículo actualizado correctamente', 200, $article);
    }

    private function resolveUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: 'articulo-wiki';
        $counter = 1;

        while (
            WikiArticle::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
