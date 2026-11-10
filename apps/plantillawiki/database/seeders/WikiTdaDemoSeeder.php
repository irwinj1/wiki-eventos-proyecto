<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Event;
use App\Models\EventSubject;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\WikiArticle;
use App\Models\WikiCategory;
use App\Models\WikiTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WikiTdaDemoSeeder extends Seeder
{
    /**
     * Crea datos iniciales completos (carreras, usuarios adicionales, categorías, tags, artículos, eventos, materias y proyectos).
     */
    public function run(): void
    {
        // 1. Carreras Universitarias
        $ingSis = Carrera::updateOrCreate(
            ['code' => 'ING-SIS'],
            ['name' => 'Ingeniería en Sistemas y Computación', 'estado' => true]
        );
        $ingSw = Carrera::updateOrCreate(
            ['code' => 'ING-SW'],
            ['name' => 'Ingeniería en Desarrollo de Software', 'estado' => true]
        );
        $tecRed = Carrera::updateOrCreate(
            ['code' => 'TEC-RED'],
            ['name' => 'Técnico en Redes y Telecomunicaciones', 'estado' => true]
        );

        // 2. Vincular usuarios base de UserSeeder con sus carreras y carnets
        $admin = User::where('email', 'admin@gmail.com')->first();
        if ($admin) {
            $admin->update(['carnet_or_code' => 'ADM-001', 'estado' => true]);
        }

        $coordinador = User::where('email', 'coordinador@gmail.com')->first();
        if ($coordinador) {
            $coordinador->update([
                'name' => 'Ing. Roberto Morales',
                'carrera_id' => $ingSis->id,
                'carnet_or_code' => 'COORD-101',
                'estado' => true,
            ]);
        }

        $teacher = User::where('email', 'teacher@gmail.com')->first();
        if ($teacher) {
            $teacher->update([
                'name' => 'Ing. Carlos Méndez',
                'carrera_id' => $ingSis->id,
                'carnet_or_code' => 'DOC-201',
                'estado' => true,
            ]);
        }

        $student = User::where('email', 'student@gmail.com')->first();
        if ($student) {
            $student->update([
                'name' => 'Ana Sofía Martínez',
                'carrera_id' => $ingSis->id,
                'carnet_or_code' => 'SM22014',
                'estado' => true,
            ]);
        }

        // Usuarios adicionales para pruebas ricas
        $teacher2 = User::updateOrCreate(
            ['email' => 'elena.rios@gmail.com'],
            [
                'name' => 'Dra. Elena Ríos',
                'password' => Hash::make('Teacher1234'),
                'role' => 'teacher',
                'carrera_id' => $ingSw->id,
                'carnet_or_code' => 'DOC-202',
                'estado' => true,
            ]
        );
        $teacher2->syncRoles(['teacher']);

        $student2 = User::updateOrCreate(
            ['email' => 'diego.perez@gmail.com'],
            [
                'name' => 'Diego Alejandro Pérez',
                'password' => Hash::make('Student1234'),
                'role' => 'student',
                'carrera_id' => $ingSis->id,
                'carnet_or_code' => 'PA22089',
                'estado' => true,
            ]
        );
        $student2->syncRoles(['student']);

        // 3. Categorías de la Wiki
        $catBackend = WikiCategory::updateOrCreate(
            ['slug' => 'desarrollo-backend'],
            [
                'name' => 'Desarrollo Backend',
                'description' => 'APIs RESTful, arquitectura de servidor y seguridad.',
                'estado' => true,
            ]
        );
        $catFrontend = WikiCategory::updateOrCreate(
            ['slug' => 'frontend-moderno'],
            [
                'name' => 'Frontend Moderno',
                'description' => 'Interfaces reactivas con Vue 3, Vuetify y gestión de estado.',
                'estado' => true,
            ]
        );
        $catDb = WikiCategory::updateOrCreate(
            ['slug' => 'base-de-datos-sql'],
            [
                'name' => 'Base de Datos y SQL',
                'description' => 'Modelado relacional, migraciones y consultas optimizadas.',
                'estado' => true,
            ]
        );
        $catNormativa = WikiCategory::updateOrCreate(
            ['slug' => 'estandares-proyectos'],
            [
                'name' => 'Estándares de Proyectos',
                'description' => 'Normativas para entrega de repositorios y documentación en eventos.',
                'estado' => true,
            ]
        );

        // 4. Etiquetas (Tags) de la Wiki
        $tagLaravel = WikiTag::updateOrCreate(['slug' => 'laravel'], ['name' => 'laravel', 'estado' => true]);
        $tagVue = WikiTag::updateOrCreate(['slug' => 'vue3'], ['name' => 'vue3', 'estado' => true]);
        $tagDocker = WikiTag::updateOrCreate(['slug' => 'docker'], ['name' => 'docker', 'estado' => true]);
        $tagGit = WikiTag::updateOrCreate(['slug' => 'git'], ['name' => 'git', 'estado' => true]);
        $tagPostgres = WikiTag::updateOrCreate(['slug' => 'postgresql'], ['name' => 'postgresql', 'estado' => true]);

        // 5. Artículos y Tutoriales de la Wiki
        if ($teacher) {
            $art1 = WikiArticle::updateOrCreate(
                ['slug' => 'guia-apis-restful-laravel-12-jwt-spatie'],
                [
                    'category_id' => $catBackend->id,
                    'author_id' => $teacher->id,
                    'type' => 'Tutorial',
                    'title' => 'Guía paso a paso: Configuración de APIs RESTful en Laravel 12 con JWT y Spatie',
                    'summary' => 'Aprende cómo estructurar controladores, proteger rutas mediante guard api y asignar roles y permisos para proyectos universitarios.',
                    'content' => "1. Introducción\nEn este tutorial revisaremos la configuración completa de autenticación JWT junto con control de acceso basado en roles (RBAC) usando Spatie Laravel Permission.\n\n2. Estructura de Rutas\nTodas las rutas administrativas se agrupan bajo auth:api.\n\n3. Respuestas Estandarizadas\nUsa ApiResponse::success() para mantener un contrato uniforme con Vue 3.",
                    'is_published' => true,
                    'estado' => true,
                ]
            );
            $art1->tags()->sync([$tagLaravel->id, $tagDocker->id]);
        }

        if ($teacher2) {
            $art2 = WikiArticle::updateOrCreate(
                ['slug' => 'arquitectura-modular-vue3-vuetify4-pinia'],
                [
                    'category_id' => $catFrontend->id,
                    'author_id' => $teacher2->id,
                    'type' => 'Artículo',
                    'title' => 'Arquitectura Modular en Vue 3 con Vuetify 4, Pinia y Axios Interceptors',
                    'summary' => 'Patrones recomendados para separar vistas, composables, stores y servicios HTTP manteniendo soporte para tema claro y oscuro.',
                    'content' => "El frontend de Wiki TDA está organizado en capas limpias:\n- services/: llamadas Axios desacopladas.\n- store/: estado reactivo global con Pinia.\n- view/: vistas modulares con composables.",
                    'is_published' => true,
                    'estado' => true,
                ]
            );
            $art2->tags()->sync([$tagVue->id]);
        }

        if ($coordinador) {
            $art3 = WikiArticle::updateOrCreate(
                ['slug' => 'normativa-entrega-repositorios-git-fesol'],
                [
                    'category_id' => $catNormativa->id,
                    'author_id' => $coordinador->id,
                    'type' => 'Tutorial',
                    'title' => 'Empaquetado de Proyectos Estudiantiles y Normativa de Repositorios Git para FESOL',
                    'summary' => 'Requisitos obligatorios para subir el código fuente, manual técnico en PDF y contenedor Docker en los eventos de fin de ciclo.',
                    'content' => "Cada equipo de proyecto registrado por el docente en su materia debe incluir:\n1. Enlace al repositorio oficial.\n2. Archivo README.md con pasos reproducibles.\n3. Documentación técnica adjunta en PDF.",
                    'is_published' => true,
                    'estado' => true,
                ]
            );
            $art3->tags()->sync([$tagGit->id, $tagDocker->id, $tagPostgres->id]);
        }

        // 6. Eventos Académicos, Materias y Proyectos Estudiantiles
        if ($coordinador && $teacher && $student) {
            $evento1 = Event::updateOrCreate(
                ['cicle_name' => 'FESOL Ciclo I - 2026'],
                [
                    'coordinador_id' => $coordinador->id,
                    'carrera_id' => $ingSis->id,
                    'description' => 'Feria Universitaria de Software Libre y Proyectos Tecnológicos Ciclo I - 2026.',
                    'start_date' => '2026-02-15',
                    'end_date' => '2026-06-30',
                    'is_active' => true,
                    'estado' => true,
                ]
            );

            $materia1 = EventSubject::updateOrCreate(
                [
                    'event_id' => $evento1->id,
                    'subject_name' => 'Ingeniería de Software II',
                ],
                [
                    'teacher_id' => $teacher->id,
                    'carrera_id' => $ingSis->id,
                    'estado' => true,
                ]
            );

            $materia2 = EventSubject::updateOrCreate(
                [
                    'event_id' => $evento1->id,
                    'subject_name' => 'Desarrollo de Aplicaciones Web',
                ],
                [
                    'teacher_id' => $teacher2->id,
                    'carrera_id' => $ingSw->id,
                    'estado' => true,
                ]
            );

            $proyecto1 = Project::updateOrCreate(
                [
                    'event_subject_id' => $materia1->id,
                    'title' => 'Sistema de Gestión Hospitalaria OpenSource con Telemedicina',
                ],
                [
                    'description' => 'Plataforma web para control de citas médicas, expediente clínico electrónico y videoconsultas en tiempo real.',
                    'repository_url' => 'https://github.com/fesol-tda/hospital-opensource',
                    'status' => 'approved',
                    'estado' => true,
                ]
            );

            ProjectMember::updateOrCreate(
                ['project_id' => $proyecto1->id, 'student_id' => $student->id],
                ['is_leader' => true, 'estado' => true]
            );
            ProjectMember::updateOrCreate(
                ['project_id' => $proyecto1->id, 'student_id' => $student2->id],
                ['is_leader' => false, 'estado' => true]
            );

            ProjectFile::updateOrCreate(
                [
                    'project_id' => $proyecto1->id,
                    'original_name' => 'Manual_Arquitectura_Hospital.pdf',
                ],
                [
                    'file_type' => 'documento',
                    'file_path' => 'projects/' . $proyecto1->id . '/Manual_Arquitectura_Hospital.pdf',
                    'file_size_kb' => 2480,
                    'extension' => 'pdf',
                    'estado' => true,
                ]
            );

            $proyecto2 = Project::updateOrCreate(
                [
                    'event_subject_id' => $materia2->id,
                    'title' => 'Repositorio Académico y Wiki Colaborativa Universitaria',
                ],
                [
                    'description' => 'Aplicación progresiva para documentación técnica estudiantil, gestión de eventos y evaluación de entregables.',
                    'repository_url' => 'https://github.com/fesol-tda/wiki-universitaria',
                    'status' => 'approved',
                    'estado' => true,
                ]
            );

            ProjectMember::updateOrCreate(
                ['project_id' => $proyecto2->id, 'student_id' => $student2->id],
                ['is_leader' => true, 'estado' => true]
            );

            ProjectFile::updateOrCreate(
                [
                    'project_id' => $proyecto2->id,
                    'original_name' => 'Especificacion_Requisitos_IEEE830.pdf',
                ],
                [
                    'file_type' => 'documento',
                    'file_path' => 'projects/' . $proyecto2->id . '/Especificacion_Requisitos_IEEE830.pdf',
                    'file_size_kb' => 3120,
                    'extension' => 'pdf',
                    'estado' => true,
                ]
            );
        }
    }
}
