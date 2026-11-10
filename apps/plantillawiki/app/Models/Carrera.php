<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Permite indicar que una relación devuelve varios registros.
use Illuminate\Database\Eloquent\Relations\HasMany;

// Modelo que representa la tabla carreras.
class Carrera extends Model
{
    // Indica explícitamente qué tabla utiliza este modelo.
    protected $table = 'carreras';

    // Estos campos pueden ser asignados mediante Carrera::create()
    // o $carrera->fill().
    protected $fillable = [
        'name',
        'code',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Una carrera puede tener muchos usuarios.
    //
    // Ejemplo:
    // Carrera 1
    // ├── Usuario Sandra
    // ├── Usuario Juan
    // └── Usuario Pedro
    //
    // Se conecta mediante:
    // carreras.id -> users.carrera_id
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'carrera_id');
    }

    // Una carrera puede aparecer en muchas materias/eventos.
    // Se conecta mediante:
    // carreras.id -> event_subjects.carrera_id
    public function eventSubjects(): HasMany
    {
        return $this->hasMany(EventSubject::class, 'carrera_id');
    }
}