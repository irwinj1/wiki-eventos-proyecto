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
     * Garantiza que incluso si las tablas ya habían sido migradas antes,
     * se agreguen las columnas faltantes (users.estado, wiki_articles.type/summary, events.carrera_id/description/end_date).
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'estado')) {
                    $table->boolean('estado')->default(true)->after('carnet_or_code');
                }
                if (!Schema::hasColumn('users', 'avatar_url')) {
                    $table->longText('avatar_url')->nullable()->after('carnet_or_code');
                }
                if (!Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('estado');
                }
            });
        }

        if (Schema::hasTable('wiki_articles')) {
            Schema::table('wiki_articles', function (Blueprint $table) {
                if (!Schema::hasColumn('wiki_articles', 'type')) {
                    $table->string('type', 20)->default('article')->after('author_id');
                }
                if (!Schema::hasColumn('wiki_articles', 'summary')) {
                    $table->string('summary', 500)->nullable()->after('slug');
                }
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                if (!Schema::hasColumn('events', 'carrera_id')) {
                    $table->foreignId('carrera_id')->nullable()->after('coordinador_id')->constrained('carreras')->nullOnDelete();
                }
                if (!Schema::hasColumn('events', 'description')) {
                    $table->text('description')->nullable()->after('cicle_name');
                }
                if (!Schema::hasColumn('events', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
            });
        }
    }

    public function down(): void
    {
        // Safe rollback
    }
};
