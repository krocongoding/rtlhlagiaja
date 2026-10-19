<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Region;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Beranda publik.
     */
    public function home()
    {
        $total = House::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->count();

        return view('public.home', compact('total'));
    }

    /**
     * Peta RTLH publik.
     */
    public function map()
    {
        return view('public.map');
    }

    /**
     * Statistik RTLH publik.
     */
    public function statistics()
    {
        $baseQuery = House::query()
            ->where('houses.is_public', true)
            ->whereIn('houses.status', ['verified', 'published']);

        /*
        |--------------------------------------------------------------------------
        | Ringkasan
        |--------------------------------------------------------------------------
        */

        $total = (clone $baseQuery)->count();

        $yearCount = (clone $baseQuery)
            ->whereNotNull('survey_year')
            ->distinct()
            ->count('survey_year');

        $districtCount = Region::kotaCirebon()
            ->where('is_active', true)
            ->where('level', 1)
            ->count();

        $villageCount = Region::kotaCirebon()
            ->where('is_active', true)
            ->where('level', 2)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | RTLH per tahun
        |--------------------------------------------------------------------------
        */

        $years = (clone $baseQuery)
            ->whereNotNull('survey_year')
            ->selectRaw('survey_year, COUNT(*) as total')
            ->groupBy('survey_year')
            ->orderBy('survey_year')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RTLH per kecamatan
        |--------------------------------------------------------------------------
        |
        | Kalau rumah tersimpan langsung di kecamatan:
        |     house.region = kecamatan
        |
        | Kalau rumah tersimpan di desa:
        |     house.region = desa
        |     desa.parent_id = kecamatan
        |
        */

        $districts = (clone $baseQuery)
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
                COALESCE(d.name, r.name) as district_name,
                COALESCE(d.code, r.code) as district_code,
                COUNT(houses.id) as total
            ")
            ->where(function ($query) {
                $query->where('r.level', 1)
                    ->orWhere('r.level', 2);
            })
            ->groupBy(
                'd.id',
                'd.name',
                'd.code',
                'r.id',
                'r.name',
                'r.code'
            )
            ->orderByDesc('total')
            ->orderBy('district_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RTLH per desa
        |--------------------------------------------------------------------------
        */

        $villages = (clone $baseQuery)
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
                v.parent_id as district_id,
                v.name as village_name,
                v.code as village_code,
                d.name as district_name,
                COUNT(houses.id) as total
            ")
            ->groupBy(
                'v.id',
                'v.parent_id',
                'v.name',
                'v.code',
                'd.name'
            )
            ->orderByDesc('total')
            ->orderBy('district_name')
            ->orderBy('village_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dropdown kecamatan
        |--------------------------------------------------------------------------
        */

        $regionDistricts = Region::kotaCirebon()
            ->where('is_active', true)
            ->where('level', 1)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return view('public.statistics', compact(
            'total',
            'yearCount',
            'districtCount',
            'villageCount',
            'years',
            'districts',
            'villages',
            'regionDistricts'
        ));
    }

    /**
     * Data RTLH publik.
     */
    public function datasets(Request $request)
    {
        $query = House::query()
            ->with('region')
            ->where('is_public', true)
            ->where('status', 'published');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(
                'house_code',
                'ilike',
                '%' . $search . '%'
            );
        }

        if ($request->filled('region_id')) {
            $query->where(
                'region_id',
                $request->integer('region_id')
            );
        }

        if ($request->filled('year')) {
            $query->where(
                'survey_year',
                $request->integer('year')
            );
        }

        $houses = $query
            ->orderByDesc('survey_year')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

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

        $years = House::query()
            ->where('is_public', true)
            ->where('status', 'published')
            ->whereNotNull('survey_year')
            ->distinct()
            ->orderByDesc('survey_year')
            ->pluck('survey_year');

        return view('public.datasets', compact(
            'houses',
            'regions',
            'years'
        ));
    }

    /**
     * Halaman Viewer.
     */
    public function viewer()
    {
        return view('public.viewer');
    }

    /**
     * Detail rumah publik.
     */
    public function detail(House $house)
    {
        abort_unless(
            $house->is_public ||
            auth()->check() ||
            in_array($house->status, ['submitted', 'verified', 'published']),
            404
        );

        $house->load([
            'region.parent',
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'floor.material',
            'floor.condition',
            'ceiling.condition',
            'wall.material',
            'wall.condition',
            'roof.frameCondition',
            'roof.material',
            'roof.condition',
            'sanitation.waterSource',
            'sanitation.toiletType',
            'sanitation.fecalDisposalType',
            'utility.lightingSource',
            'settlementCondition',
            'roomFunction',
            'ownershipStatus',
            'landStatus',
            'latestAssessment.items',
            'photos',
        ]);

        return view(
            'public.detail',
            compact('house')
        );
    }
}
