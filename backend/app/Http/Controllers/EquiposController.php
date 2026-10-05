<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\InventarioGeneral;
use App\Http\Requests\StoreEquipoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Exception;

class EquiposController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Verificación de permiso para consultar la bitácora de CPUs
        if (!$user || (!$user->can('cpus.ver_bitacora') && !$user->hasRole('admin'))) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a la bitácora de Diagnóstico CPU.');
        }

        $busqueda = trim((string)$request->query('buscar'));
        $fechaDesde = $request->query('fecha_desde');
        $fechaHasta = $request->query('fecha_hasta');
        $limite = (int) ($request->query('limite') ?? 50);
        if ($limite <= 0 || $limite > 500) {
            $limite = 50;
        }

        $query = Equipo::query();

        // Filtro de privacidad: Si tiene activo 'Ver únicamente mis propios registros y dashboard personal'
        if (!$user->hasRole('admin') && $user->tienePermiso('dashboard.ver_solo_propio')) {
            $query->where('nombre_analista', $user->nombre);
        }

        if ($busqueda) {
            $query->where(function($q) use ($busqueda) {
                $q->where('placa_id', 'like', "%{$busqueda}%")
                  ->orWhere('num_traslado', 'like', "%{$busqueda}%")
                  ->orWhere('nombre_analista', 'like', "%{$busqueda}%")
                  ->orWhere('estado_actual', 'like', "%{$busqueda}%")
                  ->orWhere('tipo_gestion', 'like', "%{$busqueda}%");
            });
        }
        if ($fechaDesde) {
            $query->where('fecha_creacion', '>=', $fechaDesde . ' 00:00:00');
        }
        if ($fechaHasta) {
            $query->where('fecha_creacion', '<=', $fechaHasta . ' 23:59:59');
        }

        $registros = $query->orderBy('id', 'desc')->paginate($limite)->withQueryString();
        $totalGeneral = (!$user->hasRole('admin') && $user->tienePermiso('dashboard.ver_solo_propio'))
            ? Equipo::where('nombre_analista', $user->nombre)->count()
            : Equipo::count();

        return view('equipos.index', [
            'registros'      => $registros,
            'totalFiltrados' => $registros->total(),
            'totalGeneral'   => $totalGeneral,
            'busqueda'       => $busqueda,
            'fechaDesde'     => $fechaDesde,
            'fechaHasta'     => $fechaHasta,
            'limite'         => $limite,
        ]);
    }

    public function create()
    {
        if (!auth()->user()->can('cpus.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para registrar nuevos diagnósticos de CPU.');
        }

        $analistas = \App\Models\Usuario::where('rol', 'analista')->where('activo', true)->get();
        return view('equipos.create', compact('analistas'));
    }

    public function store(StoreEquipoRequest $request)
    {
        if (!auth()->user()->can('cpus.registrar') && !auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para guardar diagnósticos de CPU.');
        }

        $nombreArchivo = null;
        if ($request->hasFile('evidencia') || $request->hasFile('foto') || $request->hasFile('foto_equipo') || $request->hasFile('foto_ruta')) {
            $archivo = $request->file('evidencia') ?? $request->file('foto') ?? $request->file('foto_equipo') ?? $request->file('foto_ruta');
            
            $anio = now()->format('Y');
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            $mes = $meses[(int)now()->format('n')] ?? ucfirst(now()->locale('es')->translatedFormat('F')); // Ej: Octubre
            $dia = now()->format('d'); // Ej: 05
            $modulo = 'cpus';

            // Ruta base física
            $basePath = env('EVIDENCIAS_PATH', 'C:\\Users\\LENOVO\\Pictures\\fotos');
            $basePath = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR);
            
            // Carpeta destino: C:\Users\LENOVO\Pictures\fotos\2026\Octubre\05
            $directorioDestino = $basePath . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;

            if (!File::isDirectory($directorioDestino)) {
                File::makeDirectory($directorioDestino, 0777, true, true);
            }

            // Nombre del archivo basado en la placa
            $placaRaw = $request->placa ?? $request->placa_id ?? 'EQUIPO';
            $placaLimpia = trim(strtoupper($placaRaw));
            $extension = $archivo->getClientOriginalExtension() ?: 'jpg';
            $nombreArchivo = $placaLimpia . '.' . $extension;

            // Mover físicamente el archivo
            $archivo->move($directorioDestino, $nombreArchivo);

            // Redundancia en la subcarpeta del módulo
            $directorioModulo = $basePath . DIRECTORY_SEPARATOR . $modulo . DIRECTORY_SEPARATOR . $anio . DIRECTORY_SEPARATOR . $mes . DIRECTORY_SEPARATOR . $dia;
            if (!File::isDirectory($directorioModulo)) {
                File::makeDirectory($directorioModulo, 0777, true, true);
            }
            @copy($directorioDestino . DIRECTORY_SEPARATOR . $nombreArchivo, $directorioModulo . DIRECTORY_SEPARATOR . $nombreArchivo);
        }

        $user = auth()->user();
        $nombreAnalista = ($user && $user->rol === 'analista') 
            ? $user->nombre 
            : ($request->nombre_analista ?: ($user->nombre ?? 'Sistema'));

        $serialDisco = $request->serial_disco ?: $request->serial_disco_baja;
        $descripcionNovedad = $request->descripcion_novedad ?: $request->descripcion_it;

        $novedadIt = $request->novedad_it ?: $request->descripcion_it;
        $solucionGarantias = $request->solucion_garantias;
        $timestampRegistro = $request->timestamp_registro ?: now()->toDateTimeString();

        $queVaIntervenir = $request->que_va_intervenir ?: $request->input('componentes');
        if (is_array($queVaIntervenir)) {
            $queVaIntervenir = implode(';', array_filter($queVaIntervenir));
        }

        $placaFinal = $request->placa_id ?? $request->placa;

        try {
            DB::transaction(function () use ($request, $nombreArchivo, $nombreAnalista, $serialDisco, $descripcionNovedad, $novedadIt, $solucionGarantias, $timestampRegistro, $queVaIntervenir, $placaFinal) {
                $equipo = Equipo::create([
                    'timestamp_registro'  => $timestampRegistro,
                    'nombre_analista'     => $nombreAnalista,
                    'num_traslado'        => $request->num_traslado,
                    'placa_id'            => $placaFinal,
                    'tipo_gestion'        => $request->tipo_gestion,
                    'energiza'            => $request->energiza,
                    'da_video'            => $request->da_video,
                    'estado_actual'       => $request->estado_actual,
                    'que_va_intervenir'   => $queVaIntervenir ?: null,
                    'origen_pieza'        => $request->origen_pieza,
                    'tipo_ram'            => $request->tipo_ram ?: null,
                    'marca_ram'           => $request->marca_ram ?: null,
                    'capacidad_ram'       => $request->capacidad_ram ?: null,
                    'tipo_disco'          => $request->tipo_disco ?: null,
                    'marca_disco'         => $request->marca_disco ?: null,
                    'capacidad_disco'     => $request->capacidad_disco ?: null,
                    'serial_disco'        => $serialDisco ?: null,
                    'descripcion_novedad' => $descripcionNovedad ?: null,
                    'novedad_it'          => $novedadIt ?: null,
                    'solucion_garantias'  => $solucionGarantias ?: null,
                    'motivo_baja'         => $request->motivo_baja,
                    'ubicacion_destino'   => $request->ubicacion_destino,
                    'foto_equipo'         => $nombreArchivo,
                    'evidencia'           => $nombreArchivo,
                    'fecha_creacion'      => now()
                ]);

                app(\App\Services\InventarioService::class)->marcarComoIntervenido(
                    $placaFinal,
                    'Diagnóstico CPU',
                    $nombreAnalista,
                    $request->num_traslado
                );
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            return redirect()->route('equipos.create')->with('msg', 'Guardado correctamente');

        } catch (Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
}
