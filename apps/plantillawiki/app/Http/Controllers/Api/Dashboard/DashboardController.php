<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Event;
use App\Models\EventSubject;
use App\Models\Project;
use App\Models\User;
use App\Models\WikiArticle;
use App\Models\WikiCategory;
use App\Models\WikiTag;
use App\Response\ApiResponse;

class DashboardController extends Controller
{
    /**
     * Resumen general para el Dashboard Administrativo y Portal Público.
     */
    public function stats()
    {
        try {
            $totalUsers = User::count();
            $totalCarreras = Carrera::where('estado', true)->count();
            $totalEvents = Event::where('estado', true)->count();
            $activeEvents = Event::where('estado', true)->where('is_active', true)->count();
            $totalSubjects = EventSubject::where('estado', true)->count();
            $totalProjects = Project::where('estado', true)->count();
            $pendingProjects = Project::where('estado', true)->where('status', 'pending')->count();
            $approvedProjects = Project::where('estado', true)->where('status', 'approved')->count();
            $totalArticles = WikiArticle::where('estado', true)->count();
            $publishedArticles = WikiArticle::where('estado', true)->where('is_published', true)->count();
            $totalCategories = WikiCategory::where('estado', true)->count();
            $totalTags = WikiTag::where('estado', true)->count();

            $roleCounts = [
                'admin' => User::where('role', 'admin')->count(),
                'coordinador' => User::where('role', 'coordinador')->count(),
                'teacher' => User::where('role', 'teacher')->count(),
                'student' => User::where('role', 'student')->count(),
            ];

            $latestArticles = WikiArticle::with(['category:id,name,slug', 'author:id,name,role', 'tags:id,name,slug'])
                ->where('estado', true)
                ->where('is_published', true)
                ->orderByDesc('created_at')
                ->limit(4)
                ->get();

            $latestEvents = Event::with([
                'coordinador:id,name',
                'carrera:id,name,code',
                'eventSubjects.carrera:id,name,code',
                'eventSubjects.projects',
            ])
                ->where('estado', true)
                ->orderByDesc('start_date')
                ->limit(12)
                ->get();

            return ApiResponse::success('Estadísticas del sistema', 200, [
                'counts' => [
                    'users' => $totalUsers,
                    'carreras' => $totalCarreras,
                    'events' => $totalEvents,
                    'active_events' => $activeEvents,
                    'subjects' => $totalSubjects,
                    'projects' => $totalProjects,
                    'pending_projects' => $pendingProjects,
                    'approved_projects' => $approvedProjects,
                    'articles' => $totalArticles,
                    'published_articles' => $publishedArticles,
                    'categories' => $totalCategories,
                    'tags' => $totalTags,
                ],
                'roles' => $roleCounts,
                'latest_articles' => $latestArticles,
                'latest_events' => $latestEvents,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
