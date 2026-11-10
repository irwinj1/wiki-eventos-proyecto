<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            ['id' => 1, 'name' => 'admin', 'guard_name' => 'api'],
            ['id' => 2, 'name' => 'coordinador', 'guard_name' => 'api'],
            ['id' => 3, 'name' => 'teacher', 'guard_name' => 'api'],
            ['id' => 4, 'name' => 'student', 'guard_name' => 'api'],
        ];

        foreach ($roles as $rolData) {
            $rol = Role::updateOrCreate(['id' => $rolData['id']], $rolData);

            if ($rol->name === 'admin') {
                $rol->syncPermissions([
                    'create_user', 'edit_user', 'delete_user', 'view_user',
                    'create_rol', 'edit_rol', 'delete_rol', 'view_rol',
                    'create_permission', 'edit_permission', 'delete_permission', 'view_permission',
                    'create_event', 'edit_event', 'manage_event_subjects', 'upload_projects', 'publish_wiki_articles',
                ]);
            }

            if ($rol->name === 'coordinador') {
                $rol->syncPermissions([
                    'view_user',
                    'create_event',
                    'edit_event',
                    'manage_event_subjects',
                    'publish_wiki_articles',
                ]);
            }

            if ($rol->name === 'teacher') {
                $rol->syncPermissions([
                    'view_user',
                    'manage_event_subjects',
                    'upload_projects',
                    'publish_wiki_articles',
                ]);
            }

            if ($rol->name === 'student') {
                $rol->syncPermissions([
                    'view_user',
                ]);
            }
        }
    }
}
