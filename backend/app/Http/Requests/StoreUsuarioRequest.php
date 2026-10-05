<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required', 'string', 'min:3', 'max:120'],
            'usuario'  => ['required', 'string', 'min:3', 'max:60', Rule::unique('usuarios', 'usuario')],
            'password' => ['required', 'string', 'min:6'],
            'rol_id'   => ['required', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'El nombre completo es obligatorio.',
            'nombre.min'        => 'El nombre completo debe tener al menos 3 caracteres.',
            'usuario.required'  => 'El usuario de login es obligatorio.',
            'usuario.unique'    => 'Este nombre de usuario ya está registrado en el sistema.',
            'usuario.min'       => 'El usuario de login debe tener al menos 3 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe contener al menos 6 caracteres.',
            'rol_id.required'   => 'Debe seleccionar un rol para el usuario.',
            'rol_id.exists'     => 'El rol seleccionado no es válido.',
        ];
    }
}
