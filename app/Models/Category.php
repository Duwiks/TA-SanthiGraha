<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description',
    ];

    // Relasi ke Transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    // Relasi ke Nota Merah
    public function notaMerahs()
    {
        return $this->hasMany(NotaMerah::class, 'category_id');
    }

    // Relasi ke Payment Group
    public function paymentGroups()
    {
        return $this->hasMany(PaymentGroup::class, 'category_id');
    }
}
