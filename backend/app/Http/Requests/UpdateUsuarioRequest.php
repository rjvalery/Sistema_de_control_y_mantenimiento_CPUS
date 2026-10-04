<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'   => ['required', 'string', 'min:3', 'max:120'],
            'usuario'  => ['required', 'string', 'min:3', 'max:60', Rule::unique('usuarios', 'usuario')->ignore($this->id)],
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'rol_id'   => ['required', 'exists:roles,id'],
            'activo'   => ['required', 'boolean'],
        ];
    }
}
