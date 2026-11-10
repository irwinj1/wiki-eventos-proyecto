<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiCategory extends Model
{
    // Tabla de categorías.
    protected $table = 'wiki_categories';

    // Campos permitidos para crear/modificar categorías.
    protected $fillable = [
        'name',
        'slug',
        'description',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // Una categoría puede tener muchos artículos.
    //
    // wiki_categories.id
    //       ↓
    // wiki_articles.category_id
    public function articles(): HasMany
    {
        return $this->hasMany(WikiArticle::class, 'category_id');
    }
}