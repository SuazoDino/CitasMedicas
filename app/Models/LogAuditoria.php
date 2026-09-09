<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAuditoria extends Model
{
    public $timestamps = false;

    protected $table = 'logs_auditoria';

    protected $fillable = [
        'admin_id',
        'accion',
        'tabla_afectada',
        'registro_id',
        'detalles',
        'ip_origen',
    ];

    protected function casts(): array
    {
        return [
            'detalles' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'admin_id');
    }
}
