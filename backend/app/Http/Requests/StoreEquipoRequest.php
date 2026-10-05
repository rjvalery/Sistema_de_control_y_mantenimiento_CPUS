<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $esBaja = $this->input('tipo_gestion') === 'Baja';

        return [
            'nombre_analista'     => ['required', 'string', 'max:255'],
            'num_traslado'        => ['required', 'string', 'max:100'],
            'placa_id'            => ['required_without:placa', 'nullable', 'string', 'min:2', 'max:100'],
            'placa'               => ['required_without:placa_id', 'nullable', 'string', 'min:2', 'max:100'],
            'tipo_gestion'        => ['required', 'string', 'max:100'],
            'energiza'            => ['required', 'string', 'max:50'],
            'da_video'            => ['required', 'string', 'max:50'],
            'estado_actual'       => ['required', 'string', 'max:100'],
            'que_va_intervenir'   => ['nullable'],
            'componentes'         => ['nullable', 'array'],
            'origen_pieza'        => ['nullable', 'string', 'max:100'],
            'tipo_ram'            => ['nullable', 'string', 'max:100'],
            'marca_ram'           => ['nullable', 'string', 'max:100'],
            'capacidad_ram'       => ['nullable', 'string', 'max:100'],
            'tipo_disco'          => ['nullable', 'string', 'max:100'],
            'marca_disco'         => ['nullable', 'string', 'max:100'],
            'capacidad_disco'     => ['nullable', 'string', 'max:100'],
            'serial_disco'        => ['nullable', 'string', 'max:100'],
            'serial_disco_baja'   => ['nullable', 'string', 'max:100'],
            'descripcion_novedad' => ['nullable', 'string'],
            'motivo_baja'         => ['nullable', 'string', 'max:255'],
            'novedad_it'          => ['nullable', 'string'],
            'descripcion_it'      => ['nullable', 'string'],
            'solucion_garantias'  => ['nullable', 'string'],
            'timestamp_registro'  => ['nullable', 'string', 'max:50'],
            'ubicacion_destino'   => ['required', 'string', 'max:150'],
            'foto_equipo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'evidencia'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto_ruta'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
