<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cpbm extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'address',
        'rt',
        'rw',
        'district_id',
        'region_id',
        'phone',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'district_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class, 'cpbm_id');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address];

        if ($this->rt || $this->rw) {
            $rtRw = [];
            if ($this->rt) $rtRw[] = 'RT ' . $this->rt;
            if ($this->rw) $rtRw[] = 'RW ' . $this->rw;
            $parts[] = implode(' / ', $rtRw);
        }

        if ($this->region) {
            $parts[] = 'Kel. ' . $this->region->name;
        }

        if ($this->district) {
            $parts[] = 'Kec. ' . $this->district->name;
        }

        return implode(', ', $parts);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nik', 'ilike', "%{$term}%")
                ->orWhere('name', 'ilike', "%{$term}%")
                ->orWhere('address', 'ilike', "%{$term}%");
        });
    }
}
