<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'location',
        'start_date',
        'end_date',
        'status',
    ];

    // -------------------------------------------------------
    // Scopes
    // -------------------------------------------------------

    /**
     * Project masih aktif: belum selesai DAN (end_date null atau belum lewat)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif')
                     ->where(function ($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now()->toDateString());
                     });
    }

    // -------------------------------------------------------
    // Accessors
    // -------------------------------------------------------

    /**
     * Project sudah ditandai selesai oleh admin.
     * Menggantikan logika lama yang hanya berbasis end_date.
     */
    public function getIsFinishedAttribute(): bool
    {
        return $this->status === 'selesai';
    }

    /**
     * Project jatuh tempo: end_date sudah terlewat tapi belum ditandai selesai.
     * Digunakan untuk menampilkan badge "Jatuh Tempo" dan aksi perpanjang/selesaikan.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'aktif'
            && $this->end_date !== null
            && $this->end_date < now()->toDateString();
    }

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    // Relasi ke Transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'project_id');
    }
}
