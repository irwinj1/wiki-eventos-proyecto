<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Proyecto;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Listar proyectos con filtros por evento, carrera, materia, estado y búsqueda textual.
     */
    public function index(Request $request)
    {
        try {
            $query = Project::with([
                'eventSubject.event:id,cicle_name,start_date,is_active',
                'eventSubject.carrera:id,name,code',
                'eventSubject.teacher:id,name,email',
                'members.student:id,name,email,carnet_or_code',
                'files',
            ])->where('estado', true);

            if ($request->filled('event_subject_id')) {
                $query->where('event_subject_id', $request->integer('event_subject_id'));
            }

            if ($request->filled('event_id')) {
                $eventId = $request->integer('event_id');
                $query->whereHas('eventSubject', fn ($q) => $q->where('event_id', $eventId));
            }

            if ($request->filled('carrera_id')) {
                $carreraId = $request->integer('carrera_id');
                $query->whereHas('eventSubject', fn ($q) => $q->where('carrera_id', $carreraId));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('search')) {
                $like = '%' . strtolower($request->input('search')) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$like]);
                });
            }

            $projects = $query->orderByDesc('id')->paginate(12);

            $pagination = [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ];

            return ApiResponse::success('Listado de proyectos', 200, $projects->items(), $pagination);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los proyectos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear proyecto estudiantil y opcionalmente registrar miembros, líder y subir archivos entregables en el mismo envío.
     */
    public function store(Request $request)
    {
        $payload = $this->normalizeMultipartPayload($request);

        $validator = Validator::make($payload, [
            'event_subject_id' => ['required', 'integer', 'exists:event_subjects,id'],
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'members' => ['nullable', 'array'],
            'members.*.student_id' => ['required_with:members', 'integer', 'exists:users,id'],
            'members.*.is_leader' => ['sometimes', 'boolean'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:25600', 'mimes:pdf,zip,rar,7z,doc,docx,ppt,pptx,md,txt,png,jpg,jpeg'],
            'file_types' => ['nullable', 'array'],
            'file_types.*' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $project = Project::create([
                'event_subject_id' => $payload['event_subject_id'],
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'repository_url' => $payload['repository_url'] ?? null,
                'status' => $payload['status'] ?? 'pending',
                'estado' => true,
            ]);

            if (!empty($payload['members']) && is_array($payload['members'])) {
                $this->syncMembers($project->id, $payload['members']);
            }

            $this->storeUploadedFiles($request, $project->id);

            DB::commit();

            return ApiResponse::success(
                'Proyecto registrado correctamente',
                201,
                $project->load(['eventSubject.event', 'eventSubject.carrera', 'members.student', 'files'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al crear el proyecto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar detalle completo de un proyecto.
     */
    public function show(string $id)
    {
        $project = Project::with([
            'eventSubject.event',
            'eventSubject.carrera',
            'eventSubject.teacher:id,name,email',
            'members.student:id,name,email,carnet_or_code',
            'files',
        ])->find($id);

        if (!$project) {
            return ApiResponse::error('Proyecto no encontrado', 404);
        }

        return ApiResponse::success('Proyecto encontrado', 200, $project);
    }

    /**
     * Actualizar proyecto, estado, URL de repositorio, sincronizar integrantes/líder y adjuntar nuevos archivos.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return ApiResponse::error('Proyecto no encontrado', 404);
        }

        $payload = $this->normalizeMultipartPayload($request);

        $validator = Validator::make($payload, [
            'event_subject_id' => ['sometimes', 'required', 'integer', 'exists:event_subjects,id'],
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:200'],
            'description' => ['nullable', 'string'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'members' => ['nullable', 'array'],
            'members.*.student_id' => ['required_with:members', 'integer', 'exists:users,id'],
            'members.*.is_leader' => ['sometimes', 'boolean'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:25600', 'mimes:pdf,zip,rar,7z,doc,docx,ppt,pptx,md,txt,png,jpg,jpeg'],
            'file_types' => ['nullable', 'array'],
            'file_types.*' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $project->update(collect($payload)->only([
                'event_subject_id',
                'title',
                'description',
                'repository_url',
                'status',
            ])->toArray());

            if (array_key_exists('members', $payload) && is_array($payload['members'])) {
                ProjectMember::where('project_id', $project->id)->delete();
                $this->syncMembers($project->id, $payload['members']);
            }

            $this->storeUploadedFiles($request, $project->id);

            DB::commit();

            return ApiResponse::success(
                'Proyecto actualizado correctamente',
                200,
                $project->load(['eventSubject.event', 'eventSubject.carrera', 'members.student', 'files'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al actualizar el proyecto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo del proyecto.
     */
    public function destroy(string $id)
    {
        $project = Project::find($id);
        if (!$project) {
            return ApiResponse::error('Proyecto no encontrado', 404);
        }

        $project->estado = !$project->estado;
        $project->save();

        return ApiResponse::success('Estado del proyecto actualizado correctamente', 200, $project);
    }

    /**
     * Permite recibir members como arreglo JSON o como string JSON cuando se envía FormData multipart.
     */
    private function normalizeMultipartPayload(Request $request): array
    {
        $payload = $request->all();

        if (isset($payload['members']) && is_string($payload['members'])) {
            $decoded = json_decode($payload['members'], true);
            $payload['members'] = is_array($decoded) ? $decoded : [];
        }

        if (isset($payload['repository_url']) && trim((string) $payload['repository_url']) === '') {
            $payload['repository_url'] = null;
        }

        return $payload;
    }

    /**
     * Sincroniza integrantes asegurando que solo exista un líder por proyecto.
     */
    private function syncMembers(int $projectId, array $members): void
    {
        $leaderAssigned = false;

        foreach ($members as $index => $memberData) {
            $studentId = $memberData['student_id'] ?? $memberData['id'] ?? null;
            if (!$studentId) {
                continue;
            }

            $wantsLeader = !empty($memberData['is_leader']);
            $isLeader = !$leaderAssigned && ($wantsLeader || $index === 0);
            if ($isLeader) {
                $leaderAssigned = true;
            }

            ProjectMember::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'student_id' => $studentId,
                ],
                [
                    'is_leader' => $isLeader,
                    'estado' => true,
                ]
            );
        }
    }

    /**
     * Guarda archivos físicos subidos en files[] o file junto con su categoría file_types[].
     */
    private function storeUploadedFiles(Request $request, int $projectId): void
    {
        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            $rawFiles = $request->file('files');
            $uploadedFiles = is_array($rawFiles) ? $rawFiles : [$rawFiles];
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        $fileTypes = (array) $request->input('file_types', []);
        $defaultType = $request->input('file_type', 'documento');

        foreach ($uploadedFiles as $idx => $uploaded) {
            if (!($uploaded instanceof UploadedFile) || !$uploaded->isValid()) {
                continue;
            }

            $path = $uploaded->store('projects/' . $projectId, 'public');
            $originalName = $uploaded->getClientOriginalName();
            $extension = strtolower($uploaded->getClientOriginalExtension() ?: 'bin');
            $sizeKb = max(1, (int) round($uploaded->getSize() / 1024));
            $type = $fileTypes[$idx] ?? $this->inferFileTypeByExtension($extension, $defaultType);

            ProjectFile::create([
                'project_id' => $projectId,
                'file_type' => substr((string) $type, 0, 20),
                'original_name' => $originalName,
                'file_path' => $path,
                'file_size_kb' => $sizeKb,
                'extension' => substr($extension, 0, 10),
                'estado' => true,
            ]);
        }
    }

    private function inferFileTypeByExtension(string $ext, string $fallback = 'documento'): string
    {
        if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return 'codigo';
        }
        if (in_array($ext, ['ppt', 'pptx'])) {
            return 'presentacion';
        }
        if (in_array($ext, ['pdf', 'doc', 'docx', 'md', 'txt'])) {
            return 'documento';
        }
        return $fallback;
    }
}
