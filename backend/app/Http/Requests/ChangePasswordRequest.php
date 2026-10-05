<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password_actual'    => ['required', 'string'],
            'password_nueva'     => ['required', 'string', 'min:6'],
            'password_confirmar' => ['required', 'string', 'same:password_nueva'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_actual.required'    => 'Debes ingresar tu contraseña actual.',
            'password_nueva.required'     => 'Debes ingresar la nueva contraseña.',
            'password_nueva.min'          => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'password_confirmar.required' => 'Debes confirmar la nueva contraseña.',
            'password_confirmar.same'     => 'Las contraseñas no coinciden.',
        ];
    }
}
