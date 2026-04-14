<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'category_id',
        'transaction_date',
        'type',
        'description',
        'amount',
        'payment_method',
        'receipt_photo',
        'status',
        'approved_by',
    ];

    // Relasi ke User pembuat (Pegawai)
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

    // Relasi ke User (Admin yang menyetujui)
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi ke Penolakan (bisa banyak jika sempat ditolak berulang, atau hanya hasOne)
    public function rejections()
    {
        return $this->hasMany(TransactionRejection::class, 'transaction_id');
    }
}
