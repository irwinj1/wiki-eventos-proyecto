<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_tag', function (Blueprint $table) {
            
            $table->foreignId('article_id')->constrained('wiki_articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('wiki_tags')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            
            
            // id de tabla pivote
            $table->primary(['article_id', 'tag_id']);

            
            //no muestra un id propio para article_tag, solo las dos FKs — es ltabla pivote pura por eso usé una llave primaria compuesta en vez de agregar un id autoincremental innecesario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_tag');
    }
};
