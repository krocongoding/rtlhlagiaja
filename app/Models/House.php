<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class House extends Model
{
    protected $fillable = [
        'client_uuid',
        'region_id',
        'nik',
        'head_of_household_name',
        'house_code',
        'address',
        'block',
        'rt',
        'rw',
        'area_m2',
        'occupant_count',
        'household_count',
        'survey_year',
        'status',
        'realization_status',
        'realized_at',
        'realized_by',
        'realization_notes',
        'funding_source',
        'budget_amount',
        'proposal_document_path',
        'kk_document_path',
        'akte_document_path',
        'land_document_path',
        'proposal_notes',
        'revision_notes',
        'revised_at',
        'revised_by',
        'is_public',
        'created_by',
        'updated_by',
        'location',
        'latitude',
        'longitude',
        'settlement_condition_id',
        'room_function_id',
        'ownership_status_id',
        'land_status_id',
    ];

    protected $appends = [
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'budget_amount' => 'decimal:2',
            'is_public' => 'boolean',
            'revised_at' => 'datetime',
            'realized_at' => 'datetime',
        ];
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    public function realizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realized_by');
    }

    public function assessments()
    {
        return $this->hasMany(RtlhAssessment::class);
    }

    public function latestAssessment()
    {
        return $this->hasOne(RtlhAssessment::class)
            ->latestOfMany('assessment_year');
    }

    public function structure()
    {
        return $this->hasOne(HouseStructure::class);
    }

    public function floor()
    {
        return $this->hasOne(HouseFloor::class);
    }

    public function wall()
    {
        return $this->hasOne(HouseWall::class);
    }

    public function ceiling()
    {
        return $this->hasOne(HouseCeiling::class);
    }

    public function roof()
    {
        return $this->hasOne(HouseRoof::class);
    }

    public function sanitation()
    {
        return $this->hasOne(HouseSanitation::class);
    }

    public function utility()
    {
        return $this->hasOne(HouseUtility::class);
    }

    public function occupants()
    {
        return $this->hasMany(HouseOccupant::class);
    }

    public function photos()
    {
        return $this->hasMany(HousePhoto::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data Rumah
    |--------------------------------------------------------------------------
    */

    public function settlementCondition(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'settlement_condition_id'
        );
    }

    public function roomFunction(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'room_function_id'
        );
    }

    public function ownershipStatus(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'ownership_status_id'
        );
    }

    public function landStatus(): BelongsTo
    {
        return $this->belongsTo(
            MasterValue::class,
            'land_status_id'
        );
    }

    public function getLatitudeAttribute()
    {
        if (array_key_exists('latitude', $this->attributes) && $this->attributes['latitude'] !== null && $this->attributes['latitude'] !== '') {
            return (float) $this->attributes['latitude'];
        }
        return $this->extractCoordinateFromLocation('lat');
    }

    public function getLongitudeAttribute()
    {
        if (array_key_exists('longitude', $this->attributes) && $this->attributes['longitude'] !== null && $this->attributes['longitude'] !== '') {
            return (float) $this->attributes['longitude'];
        }
        return $this->extractCoordinateFromLocation('lng');
    }

    protected function extractCoordinateFromLocation(string $type)
    {
        $location = $this->attributes['location'] ?? null;
        if (!$location) {
            return null;
        }

        if (is_object($location)) {
            if (isset($location->coordinates) && is_array($location->coordinates)) {
                return $type === 'lat' ? ($location->coordinates[1] ?? null) : ($location->coordinates[0] ?? null);
            }
        }

        if (is_string($location) && strlen($location) >= 42) {
            try {
                $bin = @hex2bin($location);
                if ($bin && strlen($bin) >= 21) {
                    $unpack = @unpack('Corder/Vtype', $bin);
                    $byteorder = $unpack['order'] ?? 1;

                    if (strlen($bin) >= 25) {
                        $parsed = @unpack($byteorder === 1 ? 'Corder/Vtype/Vsrid/dlng/dlat' : 'Corder/Ntype/Nsrid/dlng/dlat', $bin);
                        if (isset($parsed['lat']) && isset($parsed['lng'])) {
                            return $type === 'lat' ? round($parsed['lat'], 7) : round($parsed['lng'], 7);
                        }
                    }

                    $parsed = @unpack($byteorder === 1 ? 'Corder/Vtype/dlng/dlat' : 'Corder/Ntype/dlng/dlat', $bin);
                    if (isset($parsed['lat']) && isset($parsed['lng'])) {
                        return $type === 'lat' ? round($parsed['lat'], 7) : round($parsed['lng'], 7);
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    /**
     * Cek apakah program bantuan rumah sudah terealisasi.
     */
    public function isRealized(): bool
    {
        return $this->realization_status === 'realized';
    }
}