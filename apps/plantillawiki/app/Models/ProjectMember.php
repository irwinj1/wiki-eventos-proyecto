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

class ProjectMember extends Model
{
    // Tabla intermedia entre proyectos y estudiantes.
    protected $table = 'project_members';

    // Campos que pueden ser asignados.
    protected $fillable = [
        'project_id',
        'student_id',
        'is_leader',
        'estado',
    ];

    // Convierte 0/1 en false/true.
    protected $casts = [
        'is_leader' => 'boolean',
        'estado' => 'boolean',
    ];

    // Indica a qué proyecto pertenece este registro.
    //
    // project_members.project_id
    //          ↓
    // projects.id
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Indica qué usuario/estudiante pertenece al proyecto.
    //
    // project_members.student_id
    //          ↓
    // users.id
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}