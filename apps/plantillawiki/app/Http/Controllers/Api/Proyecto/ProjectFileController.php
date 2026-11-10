<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Proyecto;

use App\Http\Controllers\Controller;
use App\Models\ProjectFile;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProjectFileController extends Controller
{
    /**
     * Listar archivos de un proyecto.
     */
    public function index(Request $request)
    {
        $query = ProjectFile::query()->where('estado', true);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->integer('project_id'));
        }

        return ApiResponse::success('Archivos del proyecto', 200, $query->orderByDesc('id')->get());
    }

    /**
     * Subir o registrar un archivo asociado a un proyecto validando extensión y tamaño.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'file_type' => ['required', 'string', 'max:20'],
            'file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,zip,rar,7z,doc,docx,ppt,pptx,md,txt'],
            'original_name' => ['required_without:file', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            if ($request->hasFile('file')) {
                $uploaded = $request->file('file');
                $path = $uploaded->store('projects/' . $request->project_id, 'public');
                $originalName = $uploaded->getClientOriginalName();
                $extension = strtolower($uploaded->getClientOriginalExtension() ?: 'bin');
                $sizeKb = max(1, (int) round($uploaded->getSize() / 1024));
            } else {
                $originalName = $request->input('original_name');
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'pdf');
                $path = 'projects/' . $request->project_id . '/' . basename($originalName);
                $sizeKb = (int) $request->input('file_size_kb', 1024);
            }

            $projectFile = ProjectFile::create([
                'project_id' => $request->project_id,
                'file_type' => $request->file_type,
                'original_name' => $originalName,
                'file_path' => $path,
                'file_size_kb' => $sizeKb,
                'extension' => substr($extension, 0, 10),
                'estado' => true,
            ]);

            return ApiResponse::success('Archivo registrado correctamente', 201, $projectFile);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al subir el archivo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener metadatos o descargar un archivo de proyecto.
     */
    public function show(string $id)
    {
        $file = ProjectFile::find($id);
        if (!$file) {
            return ApiResponse::error('Archivo no encontrado', 404);
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            return Storage::disk('public')->download($file->file_path, $file->original_name);
        }

        return ApiResponse::success('Archivo encontrado', 200, $file);
    }

    /**
     * Actualizar metadatos del archivo.
     */
    public function update(Request $request, string $id)
    {
        $file = ProjectFile::find($id);
        if (!$file) {
            return ApiResponse::error('Archivo no encontrado', 404);
        }

        $file->update($request->only(['file_type', 'original_name']));

        return ApiResponse::success('Archivo actualizado correctamente', 200, $file);
    }

    /**
     * Eliminar / desactivar el archivo del proyecto.
     */
    public function destroy(string $id)
    {
        $file = ProjectFile::find($id);
        if (!$file) {
            return ApiResponse::error('Archivo no encontrado', 404);
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return ApiResponse::success('Archivo eliminado correctamente', 200);
    }
}
