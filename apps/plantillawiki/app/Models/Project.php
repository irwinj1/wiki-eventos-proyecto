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

class Project extends Model
{
    // Tabla que representa este modelo.
    protected $table = 'projects';

    // Campos que se pueden guardar.
    protected $fillable = [
        'event_subject_id',
        'title',
        'description',
        'repository_url',
        'status',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Cada proyecto pertenece a una materia/evento.
    // projects.event_subject_id
    // event_subjects.id
    public function eventSubject(): BelongsTo
    {
        return $this->belongsTo(EventSubject::class, 'event_subject_id');
    }

    // Un proyecto puede tener varios integrantes.
    // projects.id
    //     ↓
    // project_members.project_id
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class, 'project_id');
    }

    // Un proyecto puede tener varios archivos.
    // projects.id
    //      ↓
    // project_files.project_id
    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }
}