<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotaMerah extends Model
{
    use HasFactory;

    protected $table = 'nota_merah';

    protected $fillable = [
        'user_id',
        'project_id',
        'category_id',
        'description',
        'amount',
        'payment_method',
        'nota_photo',
        'realisasi_photo',
        'realisasi_date',
        'status',
        'rejection_reason',
        'approved_by',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'realisasi_date' => 'date',
    ];

    // Label status yang ramah baca
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'menunggu_persetujuan' => 'Menunggu Persetujuan',
            'disetujui'            => 'Disetujui',
            'ditolak'              => 'Ditolak',
            'menunggu_konfirmasi'  => 'Menunggu Konfirmasi',
            'selesai'              => 'Selesai',
            default                => ucfirst($this->status),
        };
    }

    // Warna badge status (kelas Tailwind)
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'menunggu_persetujuan' => 'bg-amber-100 text-amber-700',
            'disetujui'            => 'bg-blue-100 text-blue-700',
            'ditolak'              => 'bg-red-100 text-red-700',
            'menunggu_konfirmasi'  => 'bg-purple-100 text-purple-700',
            'selesai'              => 'bg-emerald-100 text-emerald-700',
            default                => 'bg-slate-100 text-slate-700',
        };
    }

    // Relasi ke Pegawai (pembuat)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Proyek
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // Relasi ke Kategori
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi ke Admin yang menyetujui/konfirmasi
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi ke Transaction yang lahir dari nota merah ini
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'nota_merah_id');
    }
}
