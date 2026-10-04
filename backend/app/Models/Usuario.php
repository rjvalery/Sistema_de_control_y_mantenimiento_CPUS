<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'usuario',
        'password',
        'rol',
        'activo',
        'permisos',
        'created_at'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Equipos analizados por este usuario
     */
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'nombre_analista', 'nombre');
    }

    /**
     * Garantías portátiles gestionadas por este usuario
     */
    public function garantiasPortatiles()
    {
        return $this->hasMany(GarantiaPortatil::class, 'nombre_analista', 'nombre');
    }

    /**
     * Soplados realizados por este usuario
     */
    public function soplados()
    {
        return $this->hasMany(SopladoRegistro::class, 'nombre_analista', 'nombre');
    }

    /**
     * Intervenciones en inventario general realizadas por este usuario
     */
    public function inventarios()
    {
        return $this->hasMany(InventarioGeneral::class, 'analista_intervencion', 'nombre');
    }

    /**
     * RBAC: Relación con Roles
     */
    public function rolesRelation()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id');
    }

    /**
     * Verifica si el usuario tiene un rol específico (por slug o nombre)
     */
    public function hasRole($roleSlug)
    {
        // Revisar si existe en la relación de base de datos
        if ($this->rolesRelation()->where('slug', $roleSlug)->exists()) {
            return true;
        }

        // Compatibilidad hacia atrás con el campo 'rol' estático original
        if ($this->rol === $roleSlug) {
            return true;
        }

        return false;
    }

    /**
     * RBAC: Relación directa con Permisos (anulaciones/asignaciones directas)
     */
    public function permisosRelation()
    {
        return $this->belongsToMany(Permiso::class, 'usuario_permiso', 'usuario_id', 'permiso_id');
    }

    /**
     * Verifica si el usuario tiene un permiso específico (directo o a través de sus roles)
     */
    public function tienePermiso($slug)
    {
        // Revisar si tiene el permiso asignado directamente
        if ($this->permisosRelation()->where('slug', $slug)->exists()) {
            return true;
        }

        // Revisar a través de sus roles
        foreach ($this->rolesRelation()->get() as $rol) {
            if ($rol->permisos()->where('slug', $slug)->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Alias para compatibilidad con código anterior
     */
    public function hasPermission($permissionSlug)
    {
        return $this->tienePermiso($permissionSlug);
    }
}
