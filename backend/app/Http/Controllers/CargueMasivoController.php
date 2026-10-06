<?php

namespace App\Http\Controllers;

use App\Models\InventarioGeneral;
use App\Models\Equipo;
use App\Models\SopladoRegistro;
use App\Models\GarantiaPortatil;
use App\Services\ExcelParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CargueMasivoController extends Controller
{
    protected ExcelParserService $parserService;

    public function __construct(ExcelParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    public function index(Request $request)
    {
        if (!in_array(Auth::user()->rol, ['admin', 'analista'])) {
            return redirect()->route('login')->with('error', 'Acceso denegado.');
        }

        return view('cargue_masivo.index', [
            'esAdmin' => Auth::user()->rol === 'admin',
        ]);
    }

    public function descargarPlantilla()
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasPermission('cargue_masivo.ejecutar')) {
            abort(403, 'No tienes permiso para descargar la plantilla de cargue.');
        }

        $delimitador = ';';
        $filename    = 'plantilla_cargue_formato_cubic.csv';

        $headers = [
            'Identificador 1', 'Identificador 2', 'Ref. Principal', 'Descripción',
            'Zona Origen', 'Ubicación Origen', 'Verificado', 'Observaciones'
        ];

        $ejemplos = [
            ['ACT-10021', 'SN-MBP99201', 'MacBook Pro 16 M1', 'Portátil corporativo Apple', 'Sede Central', 'Piso 3 - Operaciones', 'Verificado', 'Equipo en buen estado físico'],
            ['ACT-10022', 'SN-TC883011', 'ThinkCentre M70q', 'CPU de escritorio Lenovo', 'Sede Norte', 'Bodega 1 - Estante B', 'Pendiente', 'Requiere mantenimiento y soplado'],
        ];

        $output = "\xEF\xBB\xBF";
        $output .= implode($delimitador, $headers) . "\r\n";
        foreach ($ejemplos as $row) {
            $output .= implode($delimitador, $row) . "\r\n";
        }

        return response($output, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function procesar(Request $request)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasPermission('cargue_masivo.ejecutar')) {
            abort(403, 'No tienes permiso para procesar cargues masivos.');
        }

        $request->validate([
            'num_traslado' => 'required|string',
            'archivo_csv' => 'required|file',
        ], [
            'num_traslado.required' => 'El número de traslado es obligatorio.',
            'archivo_csv.required' => 'Por favor selecciona un archivo.',
        ]);

        $numTraslado = trim($request->input('num_traslado'));
        $file = $request->file('archivo_csv');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls'])) {
            return redirect()->route('inventario.index')->with('error', 'El archivo debe tener formato Excel (.xlsx) o delimitado (.csv / .txt).');
        }

        $realPath = $file->getRealPath();
        $rawHeaders = [];
        $filasExcel = [];
        $handleCsv  = null;
        $delimitador = ',';

        if ($ext === 'xlsx' || $ext === 'xls') {
            try {
                if ($ext === 'xlsx' || $this->parserService->esArchivoZip($realPath)) {
                    $datosExcel = $this->parserService->extraerFilasDesdeExcel($realPath);
                } else {
                    $datosExcel = $this->parserService->extraerFilasDesdeHtmlXls($realPath);
                    if ($datosExcel === null) {
                        return redirect()->route('inventario.index')->with('error', 'El archivo .xls tiene un formato binario antiguo. Por favor guárdalo como .xlsx o .csv.');
                    }
                }
                if (empty($datosExcel['headers'])) {
                    return redirect()->route('inventario.index')->with('error', 'El archivo Excel no contiene encabezados válidos o está vacío.');
                }
                $rawHeaders = $datosExcel['headers'];
                $filasExcel = $datosExcel['rows'];
            } catch (\Throwable $e) {
                return redirect()->route('inventario.index')->with('error', 'Error al procesar el archivo Excel: ' . $e->getMessage());
            }
        } else {
            $handleCsv = fopen($realPath, 'r');
            if (!$handleCsv) {
                return redirect()->route('inventario.index')->with('error', 'No se pudo abrir el archivo CSV para lectura.');
            }
            $bom = fread($handleCsv, 3);
            $offset = ($bom === "\xEF\xBB\xBF") ? 3 : 0;
            fseek($handleCsv, $offset);
            $delimitador = $this->parserService->detectarDelimitador($realPath, $offset);
            $rawHeaders = fgetcsv($handleCsv, 0, $delimitador);
            if (!$rawHeaders || $this->parserService->filaEstaVacia($rawHeaders)) {
                fclose($handleCsv);
                return redirect()->route('inventario.index')->with('error', 'El archivo no contiene encabezados válidos o está vacío.');
            }
        }

        $headers = [];
        foreach ($rawHeaders as $index => $h) {
            $cleaned = $this->parserService->limpiarTextoUtf8((string)$h);
            $cleaned = mb_strtolower(trim($cleaned), 'UTF-8');
            $cleaned = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $cleaned);
            $cleaned = preg_replace('/[^a-z0-9_]/', '_', $cleaned);
            $headers[$index] = trim(preg_replace('/_+/', '_', $cleaned ?? ''), '_');
        }

        $mapColumnas = $this->parserService->identificarColumnas($headers, count($rawHeaders));
        $nombreArchivo = $file->getClientOriginalName();
        $usuarioCargue = Auth::user()->nombre ?? 'Administrador';
        $ahora = now();

        $insertados       = 0;
        $errores          = 0;
        $detallesErrores  = [];
        $batchData        = [];

        $iteradorFilas = function() use ($ext, $handleCsv, $delimitador, &$filasExcel) {
            if ($ext === 'csv' || $ext === 'txt') {
                if ($handleCsv) {
                    while (($r = fgetcsv($handleCsv, 0, $delimitador)) !== false) {
                        yield $r;
                    }
                    fclose($handleCsv);
                }
            } else {
                foreach ($filasExcel as $r) {
                    yield $r;
                }
                $filasExcel = []; 
            }
        };

        foreach ($iteradorFilas() as $rawRow) {
            if ($this->parserService->filaEstaVacia($rawRow)) {
                continue;
            }

            $row = [];
            foreach ($rawRow as $idx => $val) {
                $row[$idx] = trim($this->parserService->limpiarTextoUtf8((string)$val));
            }

            $id1             = $this->parserService->obtenerValorColumna($row, $mapColumnas['id1']);
            $id2             = $this->parserService->obtenerValorColumna($row, $mapColumnas['id2']);
            $refPrincipal    = $this->parserService->obtenerValorColumna($row, $mapColumnas['refPrincipal']);
            $descripcion     = $this->parserService->obtenerValorColumna($row, $mapColumnas['descripcion']);
            $zonaOrigen      = $this->parserService->obtenerValorColumna($row, $mapColumnas['zonaOrigen']);
            $ubicacionOrigen = $this->parserService->obtenerValorColumna($row, $mapColumnas['ubicacionOrigen']);
            $verificado      = $this->parserService->obtenerValorColumna($row, $mapColumnas['verificado']);
            $observaciones   = $this->parserService->obtenerValorColumna($row, $mapColumnas['observaciones']);

            if (!$this->parserService->esIdentificadorValido($id1) && !$this->parserService->esIdentificadorValido($id2)) {
                continue;
            }
            if ($this->parserService->esFilaEncabezado($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }
            if ($this->parserService->esFilaPieDePagina($id1, $id2, $refPrincipal, $descripcion)) {
                continue;
            }

            $trasladoFila = $this->parserService->obtenerValorColumna($row, $mapColumnas['numTraslado'] ?? null);
            $trasladoFinal = !empty($trasladoFila) ? $trasladoFila : $numTraslado;

            $placaDerivada = !empty($id1) ? $id1 : (!empty($id2) ? $id2 : (!empty($refPrincipal) ? $refPrincipal : 'SIN-PLACA'));
            $serialDerivado = !empty($id2) ? $id2 : (!empty($id1) ? $id1 : 'SIN-SERIAL');
            $tipoDerivado = !empty($descripcion) ? $descripcion : 'General';
            $modeloDerivado = !empty($refPrincipal) ? $refPrincipal : (!empty($descripcion) ? $descripcion : 'Sin especificar');
            $ubicacionDerivada = trim(($zonaOrigen ?? '') . ($ubicacionOrigen ? ' - ' . $ubicacionOrigen : ''), ' -');
            $estadoDerivado = !empty($verificado) ? $verificado : 'Cargado';

            $batchData[] = [
                'identificador_1'   => $id1 !== null && $id1 !== '' ? mb_substr($id1, 0, 100, 'UTF-8') : null,
                'identificador_2'   => $id2 !== null && $id2 !== '' ? mb_substr($id2, 0, 100, 'UTF-8') : null,
                'num_traslado'      => mb_substr($trasladoFinal, 0, 100, 'UTF-8'),
                'ref_principal'     => $refPrincipal !== null && $refPrincipal !== '' ? mb_substr($refPrincipal, 0, 150, 'UTF-8') : null,
                'descripcion'       => $descripcion !== null && $descripcion !== '' ? mb_substr($descripcion, 0, 255, 'UTF-8') : null,
                'zona_origen'       => $zonaOrigen !== null && $zonaOrigen !== '' ? mb_substr($zonaOrigen, 0, 100, 'UTF-8') : null,
                'ubicacion_origen'  => $ubicacionOrigen !== null && $ubicacionOrigen !== '' ? mb_substr($ubicacionOrigen, 0, 150, 'UTF-8') : null,
                'verificado'        => $verificado !== null && $verificado !== '' ? mb_substr($verificado, 0, 50, 'UTF-8') : null,
                'observaciones'     => $observaciones !== null && $observaciones !== '' ? $observaciones : null,
                'placa_id'          => mb_substr($placaDerivada, 0, 100, 'UTF-8'),
                'serial'            => mb_substr($serialDerivado, 0, 100, 'UTF-8'),
                'tipo_equipo'       => mb_substr($tipoDerivado, 0, 80, 'UTF-8'),
                'marca'             => null,
                'modelo'            => mb_substr($modeloDerivado, 0, 150, 'UTF-8'),
                'ubicacion'         => mb_substr($ubicacionDerivada, 0, 150, 'UTF-8'),
                'estado'            => mb_substr($estadoDerivado, 0, 80, 'UTF-8'),
                'archivo_origen'    => mb_substr($nombreArchivo, 0, 255, 'UTF-8'),
                'usuario_cargue'    => mb_substr($usuarioCargue, 0, 120, 'UTF-8'),
                'created_at'        => $ahora,
                'intervenido'       => 0,
            ];

            if (count($batchData) >= 500) {
                try {
                    DB::transaction(function () use ($batchData) {
                        InventarioGeneral::insert($batchData);
                    });
                    $insertados += count($batchData);
                } catch (\Throwable $e) {
                    $errores += count($batchData);
                    if (count($detallesErrores) < 5) {
                        $detallesErrores[] = "Error en lote: " . $e->getMessage();
                    }
                }
                $batchData = [];
            }
        }

        if (count($batchData) > 0) {
            try {
                DB::transaction(function () use ($batchData) {
                    InventarioGeneral::insert($batchData);
                });
                $insertados += count($batchData);
            } catch (\Throwable $e) {
                $errores += count($batchData);
                if (count($detallesErrores) < 5) {
                    $detallesErrores[] = "Error en lote final: " . $e->getMessage();
                }
            }
        }

        if (is_resource($handleCsv)) {
            fclose($handleCsv);
        }

        if ($insertados > 0 && $errores === 0) {
            return redirect()->route('inventario.index')->with('msg', "Cargue masivo completado con éxito bajo el Traslado {$numTraslado}. Se insertaron {$insertados} registros.");
        }

        if ($insertados > 0 && $errores > 0) {
            return redirect()->route('inventario.index')->with('msg', "Cargue parcial: Se insertaron {$insertados} registros. {$errores} filas fallaron.");
        }

        return redirect()->route('inventario.index')->with('error', "No se insertaron registros. Errores: " . implode(', ', $detallesErrores));
    }

}
