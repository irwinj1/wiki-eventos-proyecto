<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    // Tabla de la base de datos que representa este modelo.
    protected $table = 'events';

    // Campos que podemos guardar mediante asignación masiva.
    protected $fillable = [
        'coordinador_id',
        'carrera_id',
        'cicle_name',
        'description',
        'start_date',
        'end_date',
        'is_active',
        'estado',
    ];

    // Convierte automáticamente estos valores.
    //
    // start_date -> objeto de fecha
    // is_active  -> true/false
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'estado' => 'boolean',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    // Cada evento tiene un coordinador.
    //
    // events.coordinador_id
    //        ↓
    // users.id
    public function coordinador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinador_id');
    }

    // Un evento puede tener varias materias.
    //
    // events.id
    //        ↓
    // event_subjects.event_id
    public function eventSubjects(): HasMany
    {
        return $this->hasMany(EventSubject::class, 'event_id');
    }
}