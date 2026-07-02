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
    ];
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now()->toDateString());
        });
    }

    public function getIsFinishedAttribute(): bool
    {
        return $this->end_date && $this->end_date < now()->toDateString();
    }

    // Relasi ke Transaksi
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'project_id');
    }
}
