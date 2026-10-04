<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortatilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_analista'                => ['required', 'string', 'max:255'],
            'numero_traslado'                => ['required', 'string', 'max:100'],
            'placa_id_equipo'                => ['required', 'string', 'min:2', 'max:100'],
            'tipo_gestion'                   => ['required', 'string', 'max:100'],
            'energiza'                       => ['required', 'string', 'max:50'],
            'da_video'                       => ['required', 'string', 'max:50'],
            'realizo_test_lenovo'            => ['required', 'string', 'max:50'],
            'estado_actual_equipo'           => ['required', 'string', 'max:100'],
            'diagnostico_laptop_intervenido' => ['nullable', 'string'],
            'garantia'                       => ['nullable', 'string', 'max:100'],
            'porque_solicita_garantia'       => ['nullable', 'string'],
            'numero_ticket'                  => ['nullable', 'string', 'max:100'],
            'estado_final_equipo'            => ['nullable', 'string', 'max:100'],
            'indique_pieza'                  => ['nullable'],
            'indique_fru'                    => ['nullable'],
            'pieza_intervenida'              => ['nullable', 'string', 'max:150'],
            'origen_pieza'                   => ['nullable', 'string', 'max:150'],
            'motivo_baja'                    => ['nullable', 'string'],
            'serial_disco'                   => ['nullable', 'string', 'max:100'],
            'reparado_por'                   => ['nullable', 'string', 'max:100'],
            'comentario_reparado'            => ['nullable', 'string'],
            'foto_equipo'                    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
