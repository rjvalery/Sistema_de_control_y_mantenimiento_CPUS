<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarLoteDiademasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Asumimos auth en el middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'numero_traslado' => 'required|string|max:100',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.identificador' => 'required|string|max:100',
            'items.*.marca_modelo' => 'nullable|string|max:255',
            'items.*.estado' => 'required|in:funcional,garantia,baja',
            'items.*.motivo' => 'nullable|string|max:255'
        ];
    }
}
