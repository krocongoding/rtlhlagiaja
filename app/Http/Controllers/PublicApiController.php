<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Region;
use App\Support\DataMasker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicApiController extends Controller
{
    /**
     * Daftar wilayah aktif untuk filter publik.
     */
    public function regions()
    {
        $regions = Region::kotaCirebon()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('name')
            ->get([
                'id',
                'parent_id',
                'code',
                'name',
                'type',
                'level',
            ]);

        return response()->json([
            'data' => $regions,
        ]);
    }

    /**
     * Data RTLH publik untuk peta.
     */
    public function rtlh(Request $request)
    {
        $query = House::query()
            ->with([
                'region',
                'structure',
                'floor',
                'wall',
                'roof',
                'sanitation',
                'utility',
                'cpbm',
            ]);

        if (auth()->check()) {
            $query->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhereIn('status', ['submitted', 'verified', 'published']);
            });
        } else {
            $query->where('is_public', true)
                ->where('status', 'published');
        } 

        /*
        |--------------------------------------------------------------------------
        | Search kode RTLH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(
                'house_code',
                'ilike',
                '%' . $search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter wilayah
        |--------------------------------------------------------------------------
        */

        if ($request->filled('region_id')) {
            $query->where(
                'region_id',
                $request->integer('region_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter tahun
        |--------------------------------------------------------------------------
        */

        if ($request->filled('year')) {
            $query->where(
                'survey_year',
                $request->integer('year')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter status realisasi
        |--------------------------------------------------------------------------
        */

        if ($request->filled('realization_status')) {
            $realStatus = $request->input('realization_status');
            if ($realStatus === 'not_realized') {
                $query->where(function ($q) {
                    $q->where('realization_status', 'not_realized')
                        ->orWhereNull('realization_status');
                });
            } else {
                $query->where('realization_status', $realStatus);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Filter data surveyor yang login (mine)
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('mine') && auth()->check()) {
            $query->where('created_by', auth()->id());
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max($request->integer('per_page', 50), 1),
            500
        );

        $rows = $query
            ->orderByDesc('survey_year')
            ->orderByDesc('id')
            ->paginate($perPage);

        $rows->getCollection()->transform(
            fn (House $house) => $this->publicHouse($house)
        );

        return response()->json($rows);
    }

    /**
     * Detail satu rumah publik.
     */
    public function show(House $house)
    {
        abort_unless(
            ($house->is_public && $house->status === 'published') ||
            auth()->check(),
            404
        );

        $house->load([
            'region',
            'structure',
            'floor',
            'wall',
            'roof',
            'ceiling',
            'sanitation',
            'utility',
            'latestAssessment',
            'photos',
        ]);

        return response()->json([
            'data' => $this->publicHouse($house, true),
        ]);
    }

    /**
     * Statistik publik.
     */
    public function statistics()
    {
        $baseQuery = House::query()
            ->where('houses.is_public', true)
            ->where('houses.status', 'published');


        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        $total = (clone $baseQuery)->count();


        /*
        |--------------------------------------------------------------------------
        | Per tahun
        |--------------------------------------------------------------------------
        */

        $byYear = (clone $baseQuery)
            ->whereNotNull('survey_year')
            ->selectRaw('survey_year, COUNT(*) as total')
            ->groupBy('survey_year')
            ->orderByDesc('survey_year')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Per kecamatan
        |--------------------------------------------------------------------------
        */

        $byDistrict = (clone $baseQuery)
            ->join(
                'regions as r',
                'r.id',
                '=',
                'houses.region_id'
            )
            ->leftJoin(
                'regions as d',
                'd.id',
                '=',
                'r.parent_id'
            )
            ->selectRaw("
                COALESCE(d.id, r.id) as district_id,
                COALESCE(d.code, r.code) as district_code,
                COALESCE(d.name, r.name) as district_name,
                COUNT(houses.id) as total
            ")
            ->where(function ($query) {
                $query->where('r.level', 1)
                    ->orWhere('r.level', 2);
            })
            ->groupBy(
                'd.id',
                'd.code',
                'd.name',
                'r.id',
                'r.code',
                'r.name'
            )
            ->orderByDesc('total')
            ->orderBy('district_name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Per desa
        |--------------------------------------------------------------------------
        */

        $byVillage = (clone $baseQuery)
            ->join(
                'regions as v',
                'v.id',
                '=',
                'houses.region_id'
            )
            ->leftJoin(
                'regions as d',
                'd.id',
                '=',
                'v.parent_id'
            )
            ->where('v.level', 2)
            ->selectRaw("
                v.id as village_id,
                v.code as village_code,
                v.name as village_name,
                v.parent_id as district_id,
                d.code as district_code,
                d.name as district_name,
                COUNT(houses.id) as total
            ")
            ->groupBy(
                'v.id',
                'v.code',
                'v.name',
                'v.parent_id',
                'd.code',
                'd.name'
            )
            ->orderByDesc('total')
            ->orderBy('district_name')
            ->orderBy('village_name')
            ->get();


        return response()->json([
            'total' => $total,

            'by_year' => $byYear,

            'by_district' => $byDistrict,

            'by_village' => $byVillage,
        ]);
    }

    /**
     * Format data rumah yang boleh dipublikasikan.
     */
    private function publicHouse(
        House $house,
        bool $detail = false
    ): array {
        /*
        |--------------------------------------------------------------------------
        | Ambil koordinat PostGIS
        |--------------------------------------------------------------------------
        */

        $coordinate = DB::selectOne(
            '
                SELECT
                    ST_X(location) AS lng,
                    ST_Y(location) AS lat
                FROM houses
                WHERE id = ?
                  AND location IS NOT NULL
            ',
            [$house->id]
        );

        $lng = $coordinate ? (float) $coordinate->lng : ($house->longitude !== null ? (float) $house->longitude : null);
        $lat = $coordinate ? (float) $coordinate->lat : ($house->latitude !== null ? (float) $house->latitude : null);

        // Jika pengunjung publik (bukan petugas yang login), samarkan koordinat GPS (~50-80m) untuk perlindungan privasi hunian warga
        if ($lng !== null && $lat !== null && ! auth()->check()) {
            [$lat, $lng] = DataMasker::fuzzCoordinate($lat, $lng, $house->id);
        }

        $data = [
            'id' => $house->id,

            'house_code' => $house->house_code,

            'region' => $house->region
                ? $house->region->only([
                    'id',
                    'code',
                    'name',
                    'type',
                ])
                : null,

            'area_m2' => $house->area_m2,

            'survey_year' => $house->survey_year,

            'status' => $house->status,

            'realization_status' => $house->realization_status ?: 'not_realized',

            'coordinates' => ($lng !== null && $lat !== null)
                ? [
                    $lng,
                    $lat,
                ]
                : null,
        ];

        if (auth()->check()) {
            $data['head_of_household_name'] = $house->head_of_household_name ?: ($house->cpbm?->name ?? 'Penerima Bantuan');
            $data['created_by'] = $house->created_by;
            $data['address'] = $house->address;
            $data['block'] = $house->block;
            $data['rt'] = $house->rt;
            $data['rw'] = $house->rw;
            $data['funding_source'] = $house->funding_source;
            $data['budget_amount'] = $house->budget_amount ? (float) $house->budget_amount : null;
            $data['realized_at'] = $house->realized_at ? $house->realized_at->format('d/m/Y') : null;
            $data['realized_score'] = $house->realized_score;
        }

        /*
        |--------------------------------------------------------------------------
        | Detail tambahan
        |--------------------------------------------------------------------------
        */

        if ($detail) {
            if (auth()->check()) {
                $data['address'] = $house->address;
                $data['block'] = $house->block;
                $data['rt'] = $house->rt;
                $data['rw'] = $house->rw;
            } else {
                $data['address'] = $house->region ? 'Wilayah ' . $house->region->name : 'Kota Cirebon';
                $data['block'] = null;
                $data['rt'] = null;
                $data['rw'] = null;
            }
            $data['ceiling'] = $house->ceiling;
            $data['structure'] = $house->structure;
            $data['floor'] = $house->floor;
            $data['wall'] = $house->wall;
            $data['roof'] = $house->roof;
            $data['sanitation'] = $house->sanitation;
            $data['utility'] = $house->utility;
            $data['latest_assessment'] = $house->latestAssessment ? [
                'score' => $house->latestAssessment->score,
                'priority_level' => $house->latestAssessment->priority_level,
                'assessment_year' => $house->latestAssessment->assessment_year,
            ] : null;

            if ($house->relationLoaded('photos')) {
                $photosBefore = $house->photos->filter(fn ($p) => $p->phase === 'before' || empty($p->phase))->map(fn ($p) => [
                    'type' => $p->type,
                    'url' => $p->path ? asset('storage/' . $p->path) : null,
                    'caption' => $p->caption,
                ])->values();

                $photosAfter = $house->photos->filter(fn ($p) => $p->phase === 'after')->map(fn ($p) => [
                    'type' => $p->type,
                    'url' => $p->path ? asset('storage/' . $p->path) : null,
                    'caption' => $p->caption,
                ])->values();

                $data['photos_before'] = $photosBefore;
                $data['photos_after'] = $photosAfter;
                $data['has_transformation'] = $house->realization_status === 'realized' && $photosAfter->isNotEmpty();
            }

            if ($house->realization_status === 'realized') {
                $data['realization'] = [
                    'status' => 'realized',
                    'funding_source' => $house->funding_source,
                    'budget_amount' => $house->budget_amount,
                    'realized_at' => $house->realized_at?->toDateString(),
                    'realized_score' => $house->realized_score ?? 0,
                    'realization_notes' => $house->realization_notes,
                ];
            }
        }

        return $data;
    }
}