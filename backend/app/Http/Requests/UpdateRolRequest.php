<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'  => ['required', 'integer', 'min:1'],
            'rol' => ['required', Rule::in(['admin', 'analista'])],
        ];
    }
}
