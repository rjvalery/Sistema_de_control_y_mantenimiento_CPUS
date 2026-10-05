<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSopladoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_analista'  => ['required', 'string', 'max:255'],
            'num_traslado'     => ['required', 'string', 'max:100'],
            'placa_id'         => ['required_without:placa', 'nullable', 'string', 'min:2', 'max:100'],
            'placa'            => ['required_without:placa_id', 'nullable', 'string', 'min:2', 'max:100'],
            'energiza'         => ['required', 'string', 'max:50'],
            'da_video'         => ['required', 'string', 'max:50'],
            'detecta_disco'    => ['required', 'string', 'max:50'],
            'ingreso_bios'     => ['required', 'string', 'max:50'],
            'pasta_termica'    => ['required', 'string', 'max:50'],
            'maquina_contenia' => ['required', 'string', 'max:100'],
            'gel_cucarachas'   => ['required_if:maquina_contenia,Cucaracha', 'nullable', 'string', 'max:50'],
            'foto_equipo'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'evidencia'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'foto_ruta'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }
}
