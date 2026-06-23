<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    protected $table = 'export_jobs';

    protected $primaryKey = 'id_export';

    protected $fillable = [
        'user_id',
        'type',
        'source',
        'filters',
        'status',
        'filename',
        'filepath',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'completed');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'absensi-excel'   => 'Excel Absensi',
            'absensi-pdf'     => 'PDF Absensi',
            'poin-excel'      => 'Excel Poin',
            'poin-pdf'        => 'PDF Poin',
            'guru-rekap-excel' => 'Excel Rekap Guru',
            default           => ucfirst($this->type),
        };
    }
}
