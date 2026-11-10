<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Roles con sus permisos asignados.
     */
    public function index()
    {
        try {
            $roles = Role::with('permissions:id,name,guard_name')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name,
                    'permissions' => $role->permissions->pluck('name')->values(),
                ];
            });
            return ApiResponse::success('Roles obtenidos correctamente', 200, $roles);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los roles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Permisos disponibles.
     */
    public function getPermissions()
    {
        try {
            $permissions = Permission::select(['id', 'name', 'guard_name'])->orderBy('id')->get();
            return ApiResponse::success('Permisos obtenidos correctamente', 200, $permissions);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener los permisos', 500);
        }
    }

    /**
     * Crear rol con lista de permisos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            if ($request->filled('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return ApiResponse::success('Rol creado correctamente', 201, [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->values(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al crear el rol: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Editar rol y sincronizar/agregar/quitar permisos.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
                'permissions_to_add' => 'nullable|array',
                'permissions_to_add.*' => 'string',
                'permissions_to_remove' => 'nullable|array',
                'permissions_to_remove.*' => 'string',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error($validator->errors()->first(), 422);
            }

            $role = Role::find($id);
            if (!$role) {
                return ApiResponse::error('Rol no encontrado', 404);
            }

            if ($request->filled('name')) {
                $role->name = $request->name;
                $role->save();
            }

            if ($request->has('permissions') && is_array($request->permissions)) {
                $role->syncPermissions($request->permissions);
            } else {
                if ($request->filled('permissions_to_add')) {
                    $role->givePermissionTo($request->permissions_to_add);
                }

                if ($request->filled('permissions_to_remove')) {
                    $role->revokePermissionTo($request->permissions_to_remove);
                }
            }

            return ApiResponse::success('Rol actualizado correctamente', 200, [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar el rol: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar rol.
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);
            if (!$role) {
                return ApiResponse::error('Rol no encontrado', 404);
            }
            $role->delete();
            return ApiResponse::success('Rol eliminado correctamente', 200, $role);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar el rol', 500);
        }
    }
}
