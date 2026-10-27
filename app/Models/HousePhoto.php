<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousePhoto extends Model
{
    protected $fillable = [
        'house_id',
        'type',
        'phase',
        'path',
        'disk',
        'caption',
        'uploaded_by',
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

    public function scopeBefore($query)
    {
        return $query->where('phase', 'before');
    }

    public function scopeAfter($query)
    {
        return $query->where('phase', 'after');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }
}
