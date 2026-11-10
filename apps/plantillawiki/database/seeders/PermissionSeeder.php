<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            ['id' => 1, 'name' => 'create_user', 'guard_name' => 'api'],
            ['id' => 2, 'name' => 'edit_user', 'guard_name' => 'api'],
            ['id' => 3, 'name' => 'delete_user', 'guard_name' => 'api'],
            ['id' => 4, 'name' => 'view_user', 'guard_name' => 'api'],

            ['id' => 5, 'name' => 'create_rol', 'guard_name' => 'api'],
            ['id' => 6, 'name' => 'edit_rol', 'guard_name' => 'api'],
            ['id' => 7, 'name' => 'delete_rol', 'guard_name' => 'api'],
            ['id' => 8, 'name' => 'view_rol', 'guard_name' => 'api'],

            ['id' => 9, 'name' => 'create_permission', 'guard_name' => 'api'],
            ['id' => 10, 'name' => 'edit_permission', 'guard_name' => 'api'],
            ['id' => 11, 'name' => 'delete_permission', 'guard_name' => 'api'],
            ['id' => 12, 'name' => 'view_permission', 'guard_name' => 'api'],

            // Permisos de negocio del sistema Wiki TDA
            ['id' => 13, 'name' => 'create_event', 'guard_name' => 'api'],
            ['id' => 14, 'name' => 'edit_event', 'guard_name' => 'api'],
            ['id' => 15, 'name' => 'manage_event_subjects', 'guard_name' => 'api'],
            ['id' => 16, 'name' => 'upload_projects', 'guard_name' => 'api'],
            ['id' => 17, 'name' => 'publish_wiki_articles', 'guard_name' => 'api'],
        ];

        foreach ($permisos as $permiso) {
            Permission::updateOrCreate(['id' => $permiso['id']], $permiso);
        }
    }
}
