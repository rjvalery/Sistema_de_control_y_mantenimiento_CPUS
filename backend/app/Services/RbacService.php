<?php

namespace App\Services;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RbacService
{
    /**
     * Catálogo canónico y unificado de permisos por módulo.
     */
    public static function getCatalogo(): array
    {
        return [
            'Diagnóstico CPUs' => [
                [
                    'slug' => 'cpus.ver_bitacora',
                    'nombre' => 'Ver bitácora',
                    'descripcion' => 'Permite consultar el listado y bitácora de CPUs diagnosticadas'
                ],
                [
                    'slug' => 'cpus.registrar',
                    'nombre' => 'Registrar diagnóstico',
                    'descripcion' => 'Permite crear nuevos diagnósticos e intervenciones de CPUs'
                ],
                [
                    'slug' => 'cpus.editar',
                    'nombre' => 'Editar diagnóstico',
                    'descripcion' => 'Permite modificar diagnósticos de CPUs previamente registrados'
                ],
                [
                    'slug' => 'cpus.eliminar',
                    'nombre' => 'Eliminar diagnóstico',
                    'descripcion' => 'Permite dar de baja o eliminar registros de CPUs'
                ],
            ],
            'Diagnóstico Portátiles' => [
                [
                    'slug' => 'portatiles.ver_bitacora',
                    'nombre' => 'Ver bitácora',
                    'descripcion' => 'Permite consultar la bitácora de garantías y diagnósticos de portátiles'
                ],
                [
                    'slug' => 'portatiles.registrar',
                    'nombre' => 'Registrar portátiles',
                    'descripcion' => 'Permite crear nuevos registros e intervenciones en laptops'
                ],
                [
                    'slug' => 'portatiles.editar',
                    'nombre' => 'Editar portátiles',
                    'descripcion' => 'Permite editar registros existentes de portátiles'
                ],
                [
                    'slug' => 'portatiles.eliminar',
                    'nombre' => 'Eliminar portátiles',
                    'descripcion' => 'Permite dar de baja o eliminar registros de portátiles'
                ],
            ],
            'Mantenimiento / Soplado' => [
                [
                    'slug' => 'soplado.ver_bitacora',
                    'nombre' => 'Ver bitácora',
                    'descripcion' => 'Permite consultar el histórico y bitácora de mantenimientos y soplados'
                ],
                [
                    'slug' => 'soplado.registrar',
                    'nombre' => 'Registrar soplado',
                    'descripcion' => 'Permite registrar nuevas labores de soplado y mantenimiento físico'
                ],
                [
                    'slug' => 'soplado.editar',
                    'nombre' => 'Editar soplado',
                    'descripcion' => 'Permite modificar registros de soplado existentes'
                ],
                [
                    'slug' => 'soplado.eliminar',
                    'nombre' => 'Eliminar soplado',
                    'descripcion' => 'Permite dar de baja o eliminar registros de soplado'
                ],
            ],
            'Inventario' => [
                [
                    'slug' => 'inventario.ver',
                    'nombre' => 'Ver Inventario General',
                    'descripcion' => 'Permite consultar, filtrar y gestionar el parque de equipos en almacén'
                ],
                [
                    'slug' => 'inventario.consultar',
                    'nombre' => 'Consulta',
                    'descripcion' => 'Permite buscar y verificar equipos en el inventario general'
                ],
                [
                    'slug' => 'inventario.traslados',
                    'nombre' => 'Traslados',
                ],
            ],
            'Cargue Masivo' => [
                [
                    'slug' => 'cargue_masivo.ejecutar',
                    'nombre' => 'Ejecutar Cargue Masivo',
                    'descripcion' => 'Permite importar planillas Excel y cargar miles de registros al inventario (Solo Administradores)'
                ],
            ],
            'Trazabilidad' => [
                [
                    'slug' => 'trazabilidad.ver',
                    'nombre' => 'Ver Módulo de Trazabilidad',
                    'descripcion' => 'Permite consultar la hoja de vida y línea de tiempo de los equipos'
                ]
            ],
            'Usuarios y Roles' => [
                [
                    'slug' => 'usuarios.ver',
                    'nombre' => 'Ver usuarios',
                    'descripcion' => 'Permite visualizar la lista de usuarios y colaboradores del sistema'
                ],
                [
                    'slug' => 'usuarios.crear',
                    'nombre' => 'Crear usuarios',
                    'descripcion' => 'Permite dar de alta nuevas cuentas de usuario'
                ],
                [
                    'slug' => 'usuarios.editar',
                    'nombre' => 'Editar usuarios',
                    'descripcion' => 'Permite modificar datos de usuarios y resetear contraseñas'
                ],
                [
                    'slug' => 'usuarios.eliminar',
                    'nombre' => 'Eliminar usuarios',
                    'descripcion' => 'Permite eliminar cuentas de usuario del sistema'
                ],
                [
                    'slug' => 'usuarios.permisos',
                    'nombre' => 'Permisos dinámicos',
                    'descripcion' => 'Permite asignar y revocar permisos individuales por usuario'
                ],
            ],
            'Dashboard / Filtros de Privacidad' => [
                [
                    'slug' => 'dashboard.ver_solo_propio',
                    'nombre' => 'Ver únicamente mis propios registros y dashboard personal',
                    'descripcion' => 'Restringe la visibilidad en bitácoras y dashboard exclusivamente a lo registrado por el analista activo'
                ],
                [
                    'slug' => 'dashboard.metricas_globales',
                    'nombre' => 'Ver métricas globales de todo el equipo',
                    'descripcion' => 'Permite ver estadísticas consolidadas y selector de analistas en el dashboard'
                ],
            ],
        ];
    }

    /**
     * Sincroniza y depura el catálogo de permisos en la base de datos.
     * Elimina duplicados, obsoletos y normaliza las relaciones.
     */
    public static function sincronizarCatalogo(): void
    {
        DB::transaction(function () {
            $catalogo = self::getCatalogo();
            $slugsCanonicos = [];

            // 1. Crear o actualizar los permisos canónicos
            foreach ($catalogo as $modulo => $items) {
                foreach ($items as $item) {
                    $slugsCanonicos[] = $item['slug'];
                    Permiso::updateOrCreate(
                        ['slug' => $item['slug']],
                        [
                            'nombre'      => $item['nombre'],
                            'modulo'      => $modulo,
                            'descripcion' => $item['descripcion']
                        ]
                    );
                }
            }

            // 2. Migrar permisos obsoletos que tengan usuarios asignados antes de eliminarlos
            $migraciones = [
                'cpus.ver'       => 'cpus.ver_bitacora',
                'portatiles.ver' => 'portatiles.ver_bitacora',
                'soplado.ver'    => 'soplado.ver_bitacora',
                'inventario.consultar' => 'inventario.ver',
                'inventario.cargue_masivo' => 'cargue_masivo.ejecutar',
            ];

            foreach ($migraciones as $antiguo => $nuevo) {
                $pAntiguo = Permiso::where('slug', $antiguo)->first();
                $pNuevo = Permiso::where('slug', $nuevo)->first();

                if ($pAntiguo && $pNuevo) {
                    $usuariosIds = DB::table('usuario_permiso')->where('permiso_id', $pAntiguo->id)->pluck('usuario_id');
                    foreach ($usuariosIds as $uId) {
                        $existe = DB::table('usuario_permiso')
                            ->where('usuario_id', $uId)
                            ->where('permiso_id', $pNuevo->id)
                            ->exists();
                        if (!$existe) {
                            DB::table('usuario_permiso')->insert([
                                'usuario_id' => $uId,
                                'permiso_id' => $pNuevo->id
                            ]);
                        }
                    }
                }
            }

            // 3. Eliminar permisos obsoletos / genéricos que generan duplicidad y confusión
            $obsoletos = [
                'diagnostico.registrar',
                'bitacora.ver',
                'registros.eliminar',
                'cpus.ver',
                'portatiles.ver',
                'soplado.ver'
            ];

            // Limpiar de rol_permiso y usuario_permiso
            $permisosABorrar = Permiso::whereIn('slug', $obsoletos)
                ->orWhereNotIn('slug', $slugsCanonicos)
                ->get();

            foreach ($permisosABorrar as $p) {
                DB::table('rol_permiso')->where('permiso_id', $p->id)->delete();
                DB::table('usuario_permiso')->where('permiso_id', $p->id)->delete();
                $p->delete();
            }

            // 4. Asignar todos los permisos canónicos al rol Administrador
            $rolAdmin = Rol::where('slug', 'admin')->first();
            if ($rolAdmin) {
                $rolAdmin->permisos()->sync(Permiso::all());
            }

            // 5. El rol 'analista' no debe tener permisos bloqueados con candado en las bitácoras obsoletas
            $rolAnalista = Rol::where('slug', 'analista')->first();
            if ($rolAnalista) {
                // Quitamos permisos residuales si los hubiere
                DB::table('rol_permiso')
                    ->where('rol_id', $rolAnalista->id)
                    ->whereNotIn('permiso_id', Permiso::pluck('id'))
                    ->delete();
            }
        });
    }
}
