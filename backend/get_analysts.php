<?php
$a = DB::table('equipos')->pluck('nombre_analista')->toArray();
$b = DB::table('soplado_registros')->pluck('nombre_analista')->toArray();
$c = DB::table('garantias_portatiles')->pluck('nombre_analista')->toArray();
$d = DB::table('inventario_general')->pluck('analista_intervencion')->toArray();
$all = array_unique(array_filter(array_merge($a, $b, $c, $d)));
echo json_encode(array_values($all));
