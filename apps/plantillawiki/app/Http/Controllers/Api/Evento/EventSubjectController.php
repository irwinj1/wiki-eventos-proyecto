<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Evento;

use App\Http\Controllers\Controller;
use App\Models\EventSubject;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventSubjectController extends Controller
{
    /**
     * Listar materias de eventos con filtro por evento, carrera o docente.
     */
    public function index(Request $request)
    {
        try {
            $query = EventSubject::with([
                'event:id,cicle_name,start_date,is_active',
                'carrera:id,name,code',
                'teacher:id,name,email',
                'projects',
            ])->withCount('projects')->where('estado', true);

            if ($request->filled('event_id')) {
                $query->where('event_id', $request->integer('event_id'));
            }
            if ($request->filled('carrera_id')) {
                $query->where('carrera_id', $request->integer('carrera_id'));
            }
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->integer('teacher_id'));
            }

            $subjects = $query->paginate(15);

            $pagination = [
                'current_page' => $subjects->currentPage(),
                'last_page' => $subjects->lastPage(),
                'per_page' => $subjects->perPage(),
                'total' => $subjects->total(),
            ];

            return ApiResponse::success('Listado de materias del evento', 200, $subjects->items(), $pagination);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener las materias: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Inscribir una materia dentro de un evento validando que el usuario asignado tenga rol de docente.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'carrera_id' => ['required', 'integer', 'exists:carreras,id'],
            'subject_name' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            $teacher = User::find($request->teacher_id);
            if ($teacher && !$teacher->hasAnyRole(['teacher', 'coordinador', 'admin']) && !in_array($teacher->role, ['teacher', 'coordinador', 'admin'])) {
                return ApiResponse::error('El usuario asignado debe tener el rol de docente (teacher).', 422);
            }

            $subject = EventSubject::create([
                'event_id' => $request->event_id,
                'teacher_id' => $request->teacher_id,
                'carrera_id' => $request->carrera_id,
                'subject_name' => $request->subject_name,
                'estado' => true,
            ]);

            return ApiResponse::success('Materia registrada correctamente en el evento', 201, $subject->load(['event', 'carrera', 'teacher']));
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar la materia: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar una materia con todos sus proyectos.
     */
    public function show(string $id)
    {
        $subject = EventSubject::with([
            'event',
            'carrera',
            'teacher:id,name,email',
            'projects.members.student:id,name,carnet_or_code',
            'projects.files',
        ])->find($id);

        if (!$subject) {
            return ApiResponse::error('Materia no encontrada', 404);
        }

        return ApiResponse::success('Materia encontrada', 200, $subject);
    }

    /**
     * Actualizar materia del evento.
     */
    public function update(Request $request, string $id)
    {
        $subject = EventSubject::find($id);
        if (!$subject) {
            return ApiResponse::error('Materia no encontrada', 404);
        }

        $validator = Validator::make($request->all(), [
            'event_id' => ['sometimes', 'required', 'integer', 'exists:events,id'],
            'teacher_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'carrera_id' => ['sometimes', 'required', 'integer', 'exists:carreras,id'],
            'subject_name' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $subject->update($request->only(['event_id', 'teacher_id', 'carrera_id', 'subject_name']));

        return ApiResponse::success('Materia actualizada correctamente', 200, $subject->load(['event', 'carrera', 'teacher']));
    }

    /**
     * Cambiar estado de la materia.
     */
    public function destroy(string $id)
    {
        $subject = EventSubject::find($id);
        if (!$subject) {
            return ApiResponse::error('Materia no encontrada', 404);
        }

        $subject->estado = !$subject->estado;
        $subject->save();

        return ApiResponse::success('Estado de la materia actualizado correctamente', 200, $subject);
    }
}
