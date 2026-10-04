<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario'  => ['required', 'string', 'min:3', 'max:60'],
            'password' => ['required', 'string', 'min:4'],
        ];
    }
}
