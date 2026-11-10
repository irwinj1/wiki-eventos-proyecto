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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WikiArticle extends Model
{
    // Tabla de artículos.
    protected $table = 'wiki_articles';

    // Campos que pueden ser asignados.
    protected $fillable = [
        'category_id',
        'author_id',
        'type',
        'title',
        'slug',
        'summary',
        'content',
        'is_published',
        'estado',
    ];

    // Convierte is_published a true/false.
    protected $casts = [
        'is_published' => 'boolean',
        'estado' => 'boolean',
    ];

    // Cada artículo pertenece a una categoría.
    //
    // wiki_articles.category_id
    //          ↓
    // wiki_categories.id
    public function category(): BelongsTo
    {
        return $this->belongsTo(WikiCategory::class, 'category_id');
    }

    // Cada artículo tiene un autor.
    //
    // wiki_articles.author_id
    //          ↓
    // users.id
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Un artículo puede tener muchas etiquetas.
    //
    // wiki_articles
    //      ↕
    // article_tag
    //      ↕
    // wiki_tags
    //
    // Es una relación muchos a muchos.
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            WikiTag::class,
            'article_tag',
            'article_id',
            'tag_id'
        );
    }
}