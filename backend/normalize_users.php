<?php

$mappings = [
    'Robert Javier Valery Barrientos' => 'Robert Valery',
    'juan sebastian cuesta daza' => 'Sebastian Cuesta',
    'Juan David Guevara' => 'Juan Guevara',
    'Luis Alejandro Bohorquez Cuao' => 'Luis Bohorquez',
    'Luis Alejandro Bohórquez cuao' => 'Luis Bohorquez',
    'Maria Camila Perdomo' => 'Camila Perdomo',
    'Camilo Andres Valencia Ariza' => 'Camilo Valencia',
    'johnbernal' => 'Jhon Bernal',
];

foreach ($mappings as $old => $new) {
    DB::table('equipos')->where('nombre_analista', $old)->update(['nombre_analista' => $new]);
    DB::table('soplado_registros')->where('nombre_analista', $old)->update(['nombre_analista' => $new]);
    DB::table('garantias_portatiles')->where('nombre_analista', $old)->update(['nombre_analista' => $new]);
    DB::table('inventario_general')->where('analista_intervencion', $old)->update(['analista_intervencion' => $new]);
}

app(\App\Services\InventarioService::class)->conciliarInventarioCompleto();

echo "Mapeo completo finalizado";
