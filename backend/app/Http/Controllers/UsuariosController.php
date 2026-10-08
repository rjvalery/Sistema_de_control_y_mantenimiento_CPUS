<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class UsuariosController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        Gate::authorize('has-permission', 'usuarios.ver');

        // Garantizar catálogo RBAC al día
        \App\Services\RbacService::sincronizarCatalogo();

        $busqueda = $request->query('buscar');
        $query = Usuario::with('rolesRelation');

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('usuario', 'like', "%{$busqueda}%");
            });
        }

        $usuarios = $query->orderBy('id', 'desc')->get();
        $roles = Rol::all();

        return response()->json(compact('usuarios', 'roles', 'busqueda'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUsuarioRequest $request)
    {
        Gate::authorize('has-permission', 'usuarios.crear');

        $rolModel = Rol::findOrFail($request->rol_id);

        $usuario = Usuario::create([
            'nombre'     => $request->nombre,
            'usuario'    => $request->usuario,
            'password'   => Hash::make($request->password),
            'activo'     => 1,
            'rol'        => $rolModel->slug, // Backward compatibility
            'created_at' => now(),
        ]);

        $usuario->rolesRelation()->attach($rolModel->id);

        return response()->json(['message' => 'Usuario creado exitosamente.']);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUsuarioRequest $request)
    {
        Gate::authorize('has-permission', 'usuarios.editar');

        $usuario = Usuario::findOrFail($request->id);

        if (auth()->id() === $usuario->id && $request->activo == 0) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        if (auth()->id() === $usuario->id && !$usuario->hasRole('admin') && Rol::find($request->rol_id)->slug !== 'admin') {
           // Si el admin se quita su propio rol de admin accidentalmente (opcional)
        }

        $rolModel = Rol::findOrFail($request->rol_id);

        $usuario->nombre = $request->nombre;
        $usuario->usuario = $request->usuario;
        $usuario->activo = $request->activo;
        $usuario->rol = $rolModel->slug; // Backward compatibility

        if (!empty($request->password)) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        $usuario->rolesRelation()->sync([$rolModel->id]);

        return response()->json(['message' => 'Usuario actualizado exitosamente.']);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy($id)
    {
        Gate::authorize('has-permission', 'usuarios.eliminar');

        $usuario = Usuario::findOrFail($id);

        // Seguridad: No permitir eliminarse a sí mismo
        if (auth()->id() === $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de usuario en sesión.');
        }

        // Seguridad: Proteger contra la eliminación del único administrador
        if ($usuario->hasRole('admin')) {
            $totalAdmins = Usuario::whereHas('rolesRelation', function ($q) {
                $q->where('slug', 'admin');
            })->orWhere('rol', 'admin')->count();

            if ($totalAdmins <= 1) {
                return back()->with('error', 'No es posible eliminar el único usuario administrador del sistema.');
            }
        }

        $nombre = $usuario->nombre;

        // Desvincular roles y permisos de forma atómica antes de eliminar
        \Illuminate\Support\Facades\DB::transaction(function () use ($usuario) {
            $usuario->rolesRelation()->detach();
            $usuario->permisosRelation()->detach();
            $usuario->delete();
        });

        return response()->json(['message' => 'El usuario ' . $nombre . ' ha sido eliminado exitosamente.']);
    }

    public function cambiarPasswordPropia(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->with('error', 'La contraseña actual es incorrecta.');
        }

        $user->update(['password' => Hash::make($request->password_nueva)]);

        return back()->with('msg', 'Contraseña actualizada con éxito.');
    }

    /**
     * Muestra la vista para editar los permisos específicos de un usuario.
     */
    public function permisos($id)
    {
        Gate::authorize('has-permission', 'usuarios.permisos');

        // Asegurar que el catálogo canónico esté sincronizado y libre de duplicados u obsoletos
        \App\Services\RbacService::sincronizarCatalogo();

        $usuario = Usuario::with(['permisosRelation', 'rolesRelation.permisos'])->findOrFail($id);
        
        // Obtener el catálogo ordenado por los 6 módulos canónicos
        $catalogoModulos = array_keys(\App\Services\RbacService::getCatalogo());
        $todosPermisos = \App\Models\Permiso::all()->groupBy('modulo');
        
        $permisosPorModulo = collect();
        foreach ($catalogoModulos as $mod) {
            if ($todosPermisos->has($mod)) {
                $permisosPorModulo->put($mod, $todosPermisos->get($mod));
            }
        }
        foreach ($todosPermisos as $mod => $items) {
            if (!$permisosPorModulo->has($mod)) {
                $permisosPorModulo->put($mod, $items);
            }
        }

        // Permisos otorgados directamente al usuario
        $permisosDirectos = $usuario->permisosRelation->pluck('id')->toArray();
        
        // Permisos que ya tiene por su rol
        $permisosPorRol = collect();
        foreach($usuario->rolesRelation as $rol) {
            $permisosPorRol = $permisosPorRol->merge($rol->permisos->pluck('id'));
        }
        $permisosPorRol = $permisosPorRol->unique()->toArray();

        return response()->json(compact('usuario', 'permisosPorModulo', 'permisosDirectos', 'permisosPorRol'));
    }

    /**
     * Sincroniza los permisos dinámicos del usuario de forma limpia y atómica.
     */
    public function guardarPermisos(Request $request, $id)
    {
        Gate::authorize('has-permission', 'usuarios.permisos');

        $usuario = Usuario::findOrFail($id);
        
        // Sanitizar array de permisos recibidos
        $permisosIds = array_filter(
            array_map('intval', (array) $request->input('permisos', []))
        );

        // Validar que los IDs existan realmente en la tabla permisos
        $permisosValidos = \App\Models\Permiso::whereIn('id', $permisosIds)->pluck('id')->toArray();

        // Guardar permisos directamente en el usuario (sincroniza sin dejar huérfanos)
        $usuario->permisosRelation()->sync($permisosValidos);

        return response()->json(['message' => 'Permisos de ' . $usuario->nombre . ' actualizados exitosamente.']);
    }
}
