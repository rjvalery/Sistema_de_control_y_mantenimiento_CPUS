<?php

namespace App\Observers;

use App\Models\InventarioGeneral;
use App\Models\HistorialAuditoria;
use Illuminate\Support\Facades\Auth;

class InventarioGeneralObserver
{
    private $auditables = ['estado', 'serial', 'placa_id', 'marca', 'modelo', 'ubicacion', 'analista_intervencion'];

    private function logAudit($event, InventarioGeneral $model)
    {
        $oldValues = [];
        $newValues = [];

        if ($event === 'updated') {
            foreach ($this->auditables as $field) {
                if ($model->isDirty($field)) {
                    $oldValues[$field] = $model->getOriginal($field);
                    $newValues[$field] = $model->getAttribute($field);
                }
            }
        } elseif ($event === 'created') {
            foreach ($this->auditables as $field) {
                $newValues[$field] = $model->getAttribute($field);
            }
        } elseif ($event === 'deleted') {
            foreach ($this->auditables as $field) {
                $oldValues[$field] = $model->getOriginal($field);
            }
        }

        if ($event !== 'updated' || !empty($newValues)) {
            HistorialAuditoria::create([
                'auditable_type' => InventarioGeneral::class,
                'auditable_id' => $model->id,
                'event' => $event,
                'user_id' => Auth::id(),
                'old_values' => empty($oldValues) ? null : $oldValues,
                'new_values' => empty($newValues) ? null : $newValues,
                'ip_address' => request()->ip(),
            ]);
        }
    }

    public function created(InventarioGeneral $inventarioGeneral): void
    {
        $this->logAudit('created', $inventarioGeneral);
    }

    public function updated(InventarioGeneral $inventarioGeneral): void
    {
        $this->logAudit('updated', $inventarioGeneral);
    }

    public function deleted(InventarioGeneral $inventarioGeneral): void
    {
        $this->logAudit('deleted', $inventarioGeneral);
    }
}
