<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        // En lugar de paginar y perder los roles en eager loading, cargamos todos con sus roles
        $usuarios = Usuario::with('rolesRelation')->orderBy('id', 'desc')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:120',
            'usuario'  => 'required|string|max:60|unique:usuarios,usuario',
            'password' => ['required', 'string', 'min:6'],
            'rol_id'   => 'required|exists:roles,id'
        ]);

        // Support for old column
        $rolModel = Rol::findOrFail($validated['rol_id']);

        $usuario = Usuario::create([
            'nombre'   => $validated['nombre'],
            'usuario'  => $validated['usuario'],
            'password' => Hash::make($validated['password']),
            'activo'   => true,
            'rol'      => $rolModel->slug, // Backward compatibility
        ]);

        // Attach new RBAC relation
        $usuario->rolesRelation()->attach($validated['rol_id']);

        return redirect()->route('usuarios.index')->with('msg', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Usuario $usuario)
    {
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:120',
            'usuario'  => 'required|string|max:60|unique:usuarios,usuario,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'rol_id'   => 'required|exists:roles,id',
            'activo'   => 'required|boolean'
        ]);

        $rolModel = Rol::findOrFail($validated['rol_id']);

        $usuario->nombre = $validated['nombre'];
        $usuario->usuario = $validated['usuario'];
        $usuario->activo = $validated['activo'];
        $usuario->rol = $rolModel->slug; // Backward compatibility
        
        if (!empty($validated['password'])) {
            $usuario->password = Hash::make($validated['password']);
        }
        
        $usuario->save();

        // Sync new RBAC relation
        $usuario->rolesRelation()->sync([$validated['rol_id']]);

        return redirect()->route('usuarios.index')->with('msg', 'Usuario actualizado exitosamente.');
    }
}
