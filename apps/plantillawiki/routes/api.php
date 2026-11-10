<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RolesController;
use App\Http\Controllers\Api\Carrera\CarreraController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\Evento\EventController;
use App\Http\Controllers\Api\Evento\EventSubjectController;
use App\Http\Controllers\Api\Proyecto\ProjectController;
use App\Http\Controllers\Api\Proyecto\ProjectFileController;
use App\Http\Controllers\Api\Usuario\UserController;
use App\Http\Controllers\Api\Wiki\WikiArticleController;
use App\Http\Controllers\Api\Wiki\WikiCategoryController;
use App\Http\Controllers\Api\Wiki\WikiTagController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

Route::post('/auth/refresh', [LoginController::class, 'refresh'])
    ->middleware('throttle:10,1');

Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/me', [LoginController::class, 'me']);
        Route::get('/profile', [LoginController::class, 'profile']);
        Route::put('/profile', [LoginController::class, 'updateProfile']);
        Route::post('/profile', [LoginController::class, 'updateProfile']);

        Route::prefix('roles')->group(function () {
            Route::get('/', [RolesController::class, 'index']);
            Route::post('/', [RolesController::class, 'store']);
            Route::put('/{id}', [RolesController::class, 'update']);
            Route::delete('/{id}', [RolesController::class, 'destroy']);
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [RolesController::class, 'getPermissions']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::post('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
    });
});

// Catálogo de Carreras (GET público; escritura protegida)
Route::prefix('carreras')->group(function () {
    Route::get('/', [CarreraController::class, 'index']);
    Route::get('/{id}', [CarreraController::class, 'showById']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [CarreraController::class, 'store']);
        Route::put('/{id}', [CarreraController::class, 'update']);
        Route::delete('/{id}', [CarreraController::class, 'destroy']);
    });
});

// Categorías de la Wiki (GET público; escritura protegida)
Route::prefix('categorias')->group(function () {
    Route::get('/', [WikiCategoryController::class, 'index']);
    Route::get('/{id}', [WikiCategoryController::class, 'showById']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [WikiCategoryController::class, 'store']);
        Route::put('/{id}', [WikiCategoryController::class, 'update']);
        Route::delete('/{id}', [WikiCategoryController::class, 'destroy']);
    });
});

// Etiquetas (Tags) de la Wiki (GET público; escritura protegida)
Route::prefix('tags')->group(function () {
    Route::get('/', [WikiTagController::class, 'index']);
    Route::get('/{id}', [WikiTagController::class, 'showById']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [WikiTagController::class, 'store']);
        Route::put('/{id}', [WikiTagController::class, 'update']);
        Route::delete('/{id}', [WikiTagController::class, 'destroy']);
    });
});

// Artículos y Tutoriales de la Wiki (GET público para lectura; escritura protegida)
Route::prefix('articulos')->group(function () {
    Route::get('/', [WikiArticleController::class, 'index']);
    Route::get('/{idOrSlug}', [WikiArticleController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [WikiArticleController::class, 'store']);
        Route::put('/{id}', [WikiArticleController::class, 'update']);
        Route::delete('/{id}', [WikiArticleController::class, 'destroy']);
    });
});

// Eventos Académicos (GET público para explorador Evento -> Carrera -> Proyectos; escritura protegida)
Route::prefix('eventos')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{id}', [EventController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [EventController::class, 'store']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
    });
});

// Materias inscritas en Eventos por Docentes (`event_subjects`)
Route::prefix('materias-evento')->group(function () {
    Route::get('/', [EventSubjectController::class, 'index']);
    Route::get('/{id}', [EventSubjectController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [EventSubjectController::class, 'store']);
        Route::put('/{id}', [EventSubjectController::class, 'update']);
        Route::delete('/{id}', [EventSubjectController::class, 'destroy']);
    });
});

        // Proyectos Estudiantiles y sus Archivos
Route::prefix('proyectos')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/{id}', [ProjectController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/crear', [ProjectController::class, 'store']);
        Route::put('/{id}', [ProjectController::class, 'update']);
        Route::post('/{id}', [ProjectController::class, 'update']);
        Route::delete('/{id}', [ProjectController::class, 'destroy']);
    });
});

Route::prefix('archivos-proyecto')->group(function () {
    Route::get('/', [ProjectFileController::class, 'index']);
    Route::get('/{id}', [ProjectFileController::class, 'show']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/subir', [ProjectFileController::class, 'store']);
        Route::put('/{id}', [ProjectFileController::class, 'update']);
        Route::delete('/{id}', [ProjectFileController::class, 'destroy']);
    });
});
