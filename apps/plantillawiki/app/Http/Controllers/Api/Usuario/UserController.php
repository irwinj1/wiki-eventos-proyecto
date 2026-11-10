<?php
/**
 * @project Wiki TDA - Plataforma Universitaria de Conocimiento, Eventos y Proyectos
 * @author Irwin Jose Maria Esperanza Guardado (Creador y Autor Original)
 * @license CC-BY-NC-SA-4.0 (Creative Commons Reconocimiento-NoComercial-CompartirIgual 4.0 Internacional)
 * @notice Prohibido patentar, cerrar el código o registrar este software o sus derivados como propiedad exclusiva de terceros.
 */

namespace App\Http\Controllers\Api\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listado paginado y filtrable de usuarios (rol, carrera, búsqueda).
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'carrera_id' => ['nullable', 'integer'],
        ]);

        $perPage = $validated['perPage'] ?? 10;

        $users = User::query()
            ->select(['id', 'carrera_id', 'name', 'email', 'role', 'carnet_or_code', 'avatar_url', 'must_change_password', 'estado'])
            ->with([
                'carrera:id,name,code',
                'roles:id,name,guard_name',
                'permissions:id,name,guard_name',
            ])
            ->when($validated['search'] ?? null, function ($query, $search) {
                $like = '%' . strtolower($search) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(COALESCE(carnet_or_code, \'\')) LIKE ?', [$like]);
                });
            })
            ->when($validated['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', fn ($q) => $q->where('name', $role));
            })
            ->when($validated['carrera_id'] ?? null, function ($query, $carreraId) {
                $query->where('carrera_id', $carreraId);
            });

        $userPaginate = $users->paginate($perPage);
        $pagination = [
            'perPage' => $userPaginate->perPage(),
            'lastPage' => $userPaginate->lastPage(),
            'currentPage' => $userPaginate->currentPage(),
            'total' => $userPaginate->total(),
        ];

        $userMap = $userPaginate->map(function ($user) {
            return [
                'id' => $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'carnet' => $user->carnet_or_code,
                'avatar_url' => $user->avatar_url,
                'must_change_password' => (bool) $user->must_change_password,
                'carrera_id' => $user->carrera_id,
                'carrera' => $user->carrera,
                'estado' => $user->estado,
                'roles' => $user->getRoleNames()->values(),
                'permisos_directos' => $user->getDirectPermissions()
                    ->pluck('name')
                    ->values(),
            ];
        });

        return ApiResponse::success('Listado de usuarios', 200, $userMap, $pagination);
    }

    /**
     * Crear usuario con contraseña temporal autogenerada, envío por correo y cambio obligatorio en primer login.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'carnet_or_code' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'estado' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $generatedPassword = $request->filled('password')
                ? $request->input('password')
                : 'Fesol#' . Str::upper(Str::random(4)) . rand(10, 99);

            $avatarUrl = $request->input('avatar_url');
            if ($request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store('avatars', 'public');
                $avatarUrl = '/storage/' . $path;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($generatedPassword),
                'role' => $request->role,
                'carrera_id' => $request->carrera_id,
                'carnet_or_code' => $request->carnet_or_code,
                'avatar_url' => $avatarUrl,
                'must_change_password' => true,
                'estado' => $request->has('estado') ? (bool) $request->estado : true,
            ]);

            $rolesToSync = $request->filled('roles') && is_array($request->roles)
                ? array_values(array_unique(array_merge([$request->role], $request->roles)))
                : [$request->role];

            $user->syncRoles($rolesToSync);

            if ($request->has('permissions') && is_array($request->permissions)) {
                $user->syncPermissions($request->permissions);
            }

            DB::commit();

            $emailSent = false;
            try {
                $body = "Hola {$user->name},\n\n"
                    . "Se ha creado tu cuenta institucional en la plataforma Wiki TDA (FESOL).\n\n"
                    . "Credenciales de acceso:\n"
                    . "• Correo electrónico: {$user->email}\n"
                    . "• Contraseña temporal: {$generatedPassword}\n"
                    . "• Rol asignado: {$user->role}\n\n"
                    . "Importante: Por motivos de seguridad, el sistema te solicitará cambiar esta contraseña temporal de forma obligatoria en tu primer inicio de sesión.\n\n"
                    . "Atentamente,\nAdministración Wiki TDA · FESOL";

                Mail::raw($body, function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Bienvenido a Wiki TDA · Credenciales de Acceso Temporal');
                });
                $emailSent = true;
            } catch (\Throwable $mailError) {
                report($mailError);
            }

            $responseData = $user->load(['carrera', 'roles', 'permissions'])->toArray();
            $responseData['generated_password'] = $generatedPassword;
            $responseData['email_sent'] = $emailSent;

            return ApiResponse::success(
                'Usuario creado y contraseña temporal enviada por correo',
                201,
                $responseData
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al crear el usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mostrar detalle de un usuario.
     */
    public function show(string $id)
    {
        $user = User::with(['carrera:id,name,code', 'roles:id,name', 'permissions:id,name'])->find($id);

        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', 404);
        }

        return ApiResponse::success('Usuario encontrado', 200, [
            'id' => $user->id,
            'nombre' => $user->name,
            'name' => $user->name,
            'email' => $user->email,
            'carnet' => $user->carnet_or_code,
            'carnet_or_code' => $user->carnet_or_code,
            'avatar_url' => $user->avatar_url,
            'must_change_password' => (bool) $user->must_change_password,
            'carrera_id' => $user->carrera_id,
            'carrera' => $user->carrera,
            'estado' => $user->estado,
            'role' => $user->role,
            'roles' => $user->getRoleNames()->values(),
            'permisos_directos' => $user->getDirectPermissions()->pluck('name')->values(),
        ]);
    }

    /**
     * Actualizar datos, rol, foto, permisos directos, estado o carrera de un usuario manteniendo sincronizado Spatie + columna role.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['sometimes', 'required', 'string', 'exists:roles,name'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'carrera_id' => ['nullable', 'integer', 'exists:carreras,id'],
            'carnet_or_code' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'string'],
            'avatar_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
            'must_change_password' => ['nullable', 'boolean'],
            'estado' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            $payload = $request->only(['name', 'email', 'carrera_id', 'carnet_or_code']);
            if ($request->filled('role')) {
                $payload['role'] = $request->role;
            }
            if ($request->has('estado')) {
                $payload['estado'] = (bool) $request->estado;
            }
            if ($request-> boolean('remove_avatar')) {
                $payload['avatar_url'] = null;
            } elseif ($request->hasFile('avatar_file')) {
                $path = $request->file('avatar_file')->store('avatars', 'public');
                $payload['avatar_url'] = '/storage/' . $path;
            } elseif ($request->has('avatar_url')) {
                $payload['avatar_url'] = $request->input('avatar_url');
            }
            if ($request->filled('password')) {
                $payload['password'] = Hash::make($request->password);
                $payload['must_change_password'] = $request->has('must_change_password')
                    ? (bool) $request->must_change_password
                    : true;
            } elseif ($request->has('must_change_password')) {
                $payload['must_change_password'] = (bool) $request->must_change_password;
            }

            $user->update($payload);

            if ($request->filled('roles') && is_array($request->roles)) {
                $primaryRole = $request->filled('role') ? $request->role : $request->roles[0];
                $user->update(['role' => $primaryRole]);
                $user->syncRoles(array_values(array_unique(array_merge([$primaryRole], $request->roles))));
            } elseif ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }

            if ($request->has('permissions') && is_array($request->permissions)) {
                $user->syncPermissions($request->permissions);
            }

            DB::commit();

            return ApiResponse::success('Usuario actualizado correctamente', 200, $user->load(['carrera', 'roles', 'permissions']));
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Error al actualizar el usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo del usuario sin romper integridad referencial.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return ApiResponse::error('Usuario no encontrado', 404);
        }

        $user->estado = !$user->estado;
        $user->save();

        return ApiResponse::success('Estado del usuario actualizado correctamente', 200, $user);
    }
}
