<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'category_id',
        'type',
        'payment_status',
        'label',
    ];

    public function scopePemasukan($query)
    {
        return $query->where('type', 'pemasukan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('type', 'pengeluaran');
    }

    // -------------------------------------------------------
    // Relations
    // -------------------------------------------------------

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'payment_group_id');
    }

    public function notaMerahs()
    {
        return $this->hasMany(NotaMerah::class, 'payment_group_id');
    }

    // -------------------------------------------------------
    // Computed Attributes
    // -------------------------------------------------------

    /**
     * Total nominal transaksi APPROVED yang tergabung dalam Payment Group ini.
     * Dihitung on-the-fly (bukan kolom cache) untuk menghindari data tidak sinkron.
     */
    public function getTotalApprovedAttribute(): float
    {
        return (float) $this->transactions()
            ->where('status', 'approved')
            ->sum('amount');
    }

    // -------------------------------------------------------
    // Business Logic
    // -------------------------------------------------------

    /**
     * Sinkronkan payment_status (cache) secara robust:
     * 1. Jika Proyek induk berstatus 'selesai' -> kelompok wajib 'selesai'.
     * 2. Jika ada minimal 1 transaksi approved 'selesai' -> kelompok 'selesai'.
     * 3. Fallback ke transaksi approved terbaru berdasarkan ID aktual.
     */
    public function syncStatus(): void
    {
        // 1. Proteksi Proyek Selesai
        if ($this->project && $this->project->status === 'selesai') {
            if ($this->payment_status !== 'selesai') {
                $this->update(['payment_status' => 'selesai']);
            }
            return;
        }

        // 2. Prioritas Status Selesai
        $hasCompleted = $this->transactions()
            ->where('status', 'approved')
            ->where('payment_stage', 'selesai')
            ->exists();

        if ($hasCompleted) {
            if ($this->payment_status !== 'selesai') {
                $this->update(['payment_status' => 'selesai']);
            }
            return;
        }

        // 3. Fallback urutan ID transaksi approved
        $latest = $this->transactions()
            ->where('status', 'approved')
            ->whereNotNull('payment_stage')
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $this->update(['payment_status' => $latest->payment_stage]);
        }
    }
}
