<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionRejection extends Model
{
    use HasFactory;

    public $timestamps = false; // Karena pada migration kita cuma definisikan 'created_at', tidak ada 'updated_at' default. Namun biarkan false biar Laravel gak otomatis ngelepas 'updated_at'.

    protected $fillable = [
        'transaction_id',
        'reason',
        'created_at',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
