<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('Admin1234'),
                'role' => 'admin',
                'estado' => true,
            ]
        );
        $user->syncRoles(['admin']);

        $user = User::updateOrCreate(
            ['email' => 'coordinador@gmail.com'],
            [
                'name' => 'coordinador',
                'password' => Hash::make('Coordinador1234'),
                'role' => 'coordinador',
                'estado' => true,
            ]
        );
        $user->syncRoles(['coordinador']);

        $user = User::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'name' => 'teacher',
                'password' => Hash::make('Teacher1234'),
                'role' => 'teacher',
                'estado' => true,
            ]
        );
        $user->syncRoles(['teacher']);

        $user = User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'name' => 'student',
                'password' => Hash::make('Student1234'),
                'role' => 'student',
                'estado' => true,
            ]
        );
        $user->syncRoles(['student']);
    }
}
