<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Evento;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Listar eventos con su coordinador, carrera y materias asociadas (y sus proyectos para vista pública/admin).
     */
    public function index(Request $request)
    {
        try {
            $query = Event::with([
                'coordinador:id,name,email',
                'carrera:id,name,code',
                'eventSubjects.carrera:id,name,code',
                'eventSubjects.teacher:id,name,email',
                'eventSubjects.projects.members.student:id,name,carnet_or_code',
                'eventSubjects.projects.files',
            ])->where('estado', true);

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('carrera_id')) {
                $carreraId = $request->integer('carrera_id');
                $query->where(function ($q) use ($carreraId) {
                    $q->where('carrera_id', $carreraId)
                        ->orWhereHas('eventSubjects', fn ($sq) => $sq->where('carrera_id', $carreraId));
                });
            }

            $events = $query->orderByDesc('start_date')->paginate(10);

            $paginationData = [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ];

            return ApiResponse::success('Listado de eventos', 200, $events->items(), $paginationData);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los eventos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear evento (solo coordinador o admin) verificando que start_date <= end_date.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coordinador_id' => ['required', 'integer', 'exists:users,id'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'cicle_name' => ['required', 'string', 'min:3', 'max:50'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            $coordinador = User::find($request->coordinador_id);
            if ($coordinador && !$coordinador->hasAnyRole(['coordinador', 'admin']) && !in_array($coordinador->role, ['coordinador', 'admin'])) {
                return ApiResponse::error('El usuario asignado debe tener el rol de coordinador o administrador.', 422);
            }

            $event = Event::create([
                'coordinador_id' => $request->coordinador_id,
                'carrera_id' => $request->carrera_id ?? $coordinador?->carrera_id,
                'cicle_name' => $request->cicle_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->boolean('is_active', true),
                'estado' => true,
            ]);

            return ApiResponse::success('Evento creado correctamente', 201, $event->load(['coordinador:id,name', 'carrera:id,name,code']));
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear el evento: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Detalle de un evento con sus carreras, materias y proyectos.
     */
    public function show(string $id)
    {
        $event = Event::with([
            'coordinador:id,name,email',
            'carrera:id,name,code',
            'eventSubjects.carrera:id,name,code',
            'eventSubjects.teacher:id,name,email',
            'eventSubjects.projects.members.student:id,name,carnet_or_code',
            'eventSubjects.projects.files',
        ])->find($id);

        if (!$event) {
            return ApiResponse::error('Evento no encontrado', 404);
        }

        return ApiResponse::success('Evento encontrado', 200, $event);
    }

    /**
     * Actualizar evento o activar/desactivar.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return ApiResponse::error('Evento no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'coordinador_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'cicle_name' => ['sometimes', 'required', 'string', 'min:3', 'max:50'],
            'description' => ['nullable', 'string'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'end_date.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $event->update($request->only([
            'coordinador_id',
            'carrera_id',
            'cicle_name',
            'description',
            'start_date',
            'end_date',
            'is_active',
        ]));

        return ApiResponse::success('Evento actualizado correctamente', 200, $event->load(['coordinador:id,name', 'carrera:id,name,code']));
    }

    /**
     * Alternar estado del evento.
     */
    public function destroy(string $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return ApiResponse::error('Evento no encontrado', 404);
        }

        $event->estado = !$event->estado;
        $event->save();

        return ApiResponse::success('Estado del evento actualizado correctamente', 200, $event);
    }
}
