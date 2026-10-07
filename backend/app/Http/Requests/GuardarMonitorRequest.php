<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarMonitorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'serial' => 'required|string|max:100',
            'placa' => 'nullable|string|max:100',
            'numero_traslado' => 'nullable|string|max:100',
            'tipo_gestion' => 'required|in:diagnostico,novedad,baja',
            'energiza' => 'required_if:tipo_gestion,diagnostico|nullable|boolean',
            'da_video' => 'required_if:tipo_gestion,diagnostico|nullable|boolean',
            'motivo_novedad' => 'required_if:tipo_gestion,novedad|nullable|string|min:5',
            'estado_actual' => 'required|in:funcional,garantia,baja',
            'observaciones' => 'nullable|string',
        ];
    }
}
