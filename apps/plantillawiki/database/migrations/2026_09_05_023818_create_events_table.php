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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            //se agrego desde aca
            $table->foreignId('coordinador_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras')->nullOnDelete();
            $table->string('cicle_name', 50);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('estado')->default(true);
            // hasta aca
            $table->timestamps();
        });
    }

    //coordinator_id con restrictOnDelete no deja borrar a un usuario si es coordinador de algún evento existente  un evento sin coordinador no tendria  responsable




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
