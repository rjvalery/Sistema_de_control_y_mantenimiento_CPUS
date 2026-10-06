<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class BitacoraExportService
{
    /**
     * Genera una respuesta HTTP tipo StreamedResponse para descargar un CSV
     * iterando sobre los resultados con bajo consumo de memoria.
     *
     * @param string $nombreArchivo
     * @param array $encabezados
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param \Closure $callbackFila Callback que recibe ($registro) y retorna un array con las columnas
     * @return StreamedResponse
     */
    public function exportarCsvStream(string $nombreArchivo, array $encabezados, $query, \Closure $callbackFila): StreamedResponse
    {
        $headersHttp = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callbackStream = function () use ($encabezados, $query, $callbackFila) {
            $handle = fopen('php://output', 'w');

            // Insertar BOM UTF-8 para que Excel reconozca los caracteres especiales (tildes, ñ)
            fwrite($handle, "\xEF\xBB\xBF");

            // Escribir los encabezados (usamos ';' porque en español es el delimitador por defecto de Excel)
            fputcsv($handle, $encabezados, ';');

            // Iterar los resultados usando cursor() para evitar picos de memoria (RAM)
            foreach ($query->cursor() as $registro) {
                $filaTransformada = $callbackFila($registro);
                fputcsv($handle, $filaTransformada, ';');
            }

            fclose($handle);
        };

        return response()->stream($callbackStream, 200, $headersHttp);
    }
}
