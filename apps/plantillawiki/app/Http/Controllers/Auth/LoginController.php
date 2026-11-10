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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    /**
     * Login
     *
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            $user = auth('api')->user();
            if ($user && $user->estado === false) {
                auth('api')->logout();
                return response()->json([
                    'message' => 'Tu cuenta se encuentra inactiva. Contacta al administrador.',
                ], 403);
            }
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
            $data['token'] = $token;
            $data['user'] = $user;
            $data['must_change_password'] = (bool) $user->must_change_password;

            return ApiResponse::success('Inicio de sesión exitoso', 200, $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al iniciar sesión', 500);
        }
    }

    /**
     * Logout
     *
     */
    public function logout(){
        try {
            auth('api')->logout();
            return ApiResponse::success('Sesión cerrada');
        } catch (JWTException $e) {
            return ApiResponse::error('La sesión no pudo invalidarse', 401);
        } catch (\Throwable $e) {
            report($e);
            return ApiResponse::error('Error al cerrar sesión', 500);
        }
    }

    /**
     * Refresh
     *
     */
    public function refresh()
    {
        try {
            $guard = auth('api');
            $token = $guard->refresh();
            $user = $guard->setToken($token)->user();

            if (! $user) {
                return ApiResponse::error('La sesión ya no es válida', 401);
            }

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return ApiResponse::success('Token actualizado', 200, [
                'token' => $token,
                'user' => $user,
            ]);
        } catch (JWTException $e) {
            return ApiResponse::error('La sesión expiró. Inicia sesión nuevamente.', 401);
        } catch (\Throwable $e) {
            report($e);
            return ApiResponse::error('Error al renovar la sesión', 500);
        }
    }

    public function me()
    {
        try {
            $user = auth('api')->user();
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el usuario',
            ], 500);
        }
    }

    /**
     * Obtener perfil completo del usuario autenticado (con carrera, roles y permisos).
     */
    public function profile()
    {
        try {
            $user = auth('api')->user();
            if (! $user) {
                return ApiResponse::error('No autenticado', 401);
            }

            $user->load(['carrera:id,name,code']);

            return ApiResponse::success('Perfil obtenido correctamente', 200, [
                'id' => $user->id,
                'name' => $user->name,
                'nombre' => $user->name,
                'email' => $user->email,
                'carnet_or_code' => $user->carnet_or_code,
                'carnet' => $user->carnet_or_code,
                'avatar_url' => $user->avatar_url,
                'must_change_password' => (bool) $user->must_change_password,
                'role' => $user->role,
                'carrera_id' => $user->carrera_id,
                'carrera' => $user->carrera,
                'estado' => $user->estado,
                'roles' => $user->getRoleNames()->values(),
                'permisos_directos' => $user->getDirectPermissions()->pluck('name')->values(),
                'permisos' => $user->getAllPermissions()->pluck('name')->values(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener el perfil: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar perfil propio por el usuario autenticado (datos personales, carrera y contraseña).
     */
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        if (! $user) {
            return ApiResponse::error('No autenticado', 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'carnet_or_code' => ['nullable', 'string', 'max:50'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'avatar_url' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error('La contraseña actual no coincide con nuestros registros.', 422);
            }
        }

        try {
            $payload = $request->only(['name', 'email', 'carnet_or_code', 'carrera_id']);

            if ($request->boolean('remove_avatar')) {
                $payload['avatar_url'] = null;
            } elseif ($request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store('avatars', 'public');
                $payload['avatar_url'] = '/storage/' . $path;
            } elseif ($request->has('avatar_url')) {
                $payload['avatar_url'] = $request->input('avatar_url');
            }

            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->password);
                $payload['must_change_password'] = false;
            }

            $user->update($payload);
            $user->load(['carrera:id,name,code']);

            return ApiResponse::success('Perfil actualizado correctamente', 200, [
                'id' => $user->id,
                'name' => $user->name,
                'nombre' => $user->name,
                'email' => $user->email,
                'carnet_or_code' => $user->carnet_or_code,
                'carnet' => $user->carnet_or_code,
                'avatar_url' => $user->avatar_url,
                'must_change_password' => (bool) $user->must_change_password,
                'role' => $user->role,
                'carrera_id' => $user->carrera_id,
                'carrera' => $user->carrera,
                'estado' => $user->estado,
                'roles' => $user->getRoleNames()->values(),
                'permisos_directos' => $user->getDirectPermissions()->pluck('name')->values(),
                'permisos' => $user->getAllPermissions()->pluck('name')->values(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar el perfil: ' . $e->getMessage(), 500);
        }
    }
}
