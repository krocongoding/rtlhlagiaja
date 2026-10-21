<?php

namespace App\Http\Controllers;

use App\Http\Requests\HouseRequest;
use App\Models\House;
use App\Models\MasterCategory;
use App\Models\Region;
use App\Services\HouseDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::with(['region'])
            ->latest('id');

        if (! auth()->user()->hasRole('admin')) {
            $query->where('created_by', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('house_code', 'ilike', "%{$search}%")
                    ->orWhere('address', 'ilike', "%{$search}%")
                    ->orWhere('nik', 'ilike', "%{$search}%")
                    ->orWhere('head_of_household_name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled('realization_status')) {
            $status = $request->string('realization_status')->toString();
            if ($status === 'realized') {
                $query->where('realization_status', 'realized');
            } elseif ($status === 'not_realized') {
                $query->where(function ($q) {
                    $q->where('realization_status', '!=', 'realized')
                        ->orWhereNull('realization_status');
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $houses = $query
            ->paginate(15)
            ->withQueryString();

        $totalHouses = House::count();
        $realizedCount = House::where('realization_status', 'realized')->count();
        $notRealizedCount = House::where(function ($q) {
            $q->where('realization_status', '!=', 'realized')
                ->orWhereNull('realization_status');
        })->count();

        // Scope base for quick tab counts
        $baseScope = House::query();
        if (! auth()->user()->hasRole('admin')) {
            $baseScope->where('created_by', auth()->id());
        }

        $tabCounts = [
            'all' => (clone $baseScope)->count(),
            'submitted' => (clone $baseScope)->where('status', 'submitted')->count(),
            'revision' => (clone $baseScope)->where('status', 'revision')->count(),
            'verified' => (clone $baseScope)->where('status', 'verified')->count(),
            'published' => (clone $baseScope)->where('status', 'published')->count(),
            'draft' => (clone $baseScope)->where('status', 'draft')->count(),
            'realized' => (clone $baseScope)->where('realization_status', 'realized')->count(),
        ];

        $villages = Region::kotaCirebon()
            ->whereIn('type', ['village', 'desa'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('houses.index', compact(
            'houses',
            'totalHouses',
            'realizedCount',
            'notRealizedCount',
            'villages',
            'tabCounts'
        ));
    }

    public function create(Request $request)
    {
        $cats = MasterCategory::with([
            'values' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->get()
        ->keyBy('code');

        $regions = Region::kotaCirebon()
            ->where('is_active', true)
            ->whereIn('type', ['village', 'desa'])
            ->orderBy('name')
            ->get();

        $house = new House();
        if ($request->filled('cpbm_id')) {
            $cpbm = \App\Models\Cpbm::with('region')->find($request->integer('cpbm_id'));
            if ($cpbm) {
                $house->cpbm_id = $cpbm->id;
                $house->nik = $cpbm->nik;
                $house->head_of_household_name = $cpbm->name;
                $house->address = $cpbm->address;
                $house->rt = $cpbm->rt;
                $house->rw = $cpbm->rw;
                $house->region_id = $cpbm->region_id;
                $house->setRelation('cpbm', $cpbm);
            }
        }

        return view('houses.form', [
            'house' => $house,
            'regions' => $regions,
            'cats' => $cats,
            'action' => route('houses.store'),
            'method' => 'POST',
            'panel' => 'admin',
        ]);
    }

    public function store(HouseRequest $request, HouseDataService $service)
    {
        $validated = $request->validated();
        $submit = $request->boolean('submit');

        $house = House::create([
            ...$validated,
            'house_code' => $service->generateUniqueCode($validated['region_id']),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'status' => 'draft',
            'is_public' => false,
        ]);

        $service->save($house, $validated, auth()->id(), $submit);

        if ($request->hasFile('photo_front') || $request->hasFile('photo_angle') || $request->hasFile('photo_side') || $request->hasFile('photo_back') || $request->hasFile('photo_family_room') || $request->hasFile('photo_bathroom')) {
            $service->storePhotos($house, $request->allFiles(), auth()->id());
        }

        return redirect()
            ->route('houses.show', $house)
            ->with('success', $submit ? 'Data RTLH berhasil ditambahkan.' : 'Data RTLH berhasil disimpan sebagai draft.');
    }

    public function edit(House $house)
    {
        $house->load([
            'cpbm',
            'structure',
            'floor',
            'wall',
            'ceiling',
            'roof',
            'sanitation',
            'utility',
            'photos',
        ]);

        $cats = MasterCategory::with([
            'values' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->get()
        ->keyBy('code');

        $regions = Region::kotaCirebon()
            ->where('is_active', true)
            ->whereIn('type', ['village', 'desa'])
            ->orderBy('name')
            ->get();

        return view('houses.form', [
            'house' => $house,
            'regions' => $regions,
            'cats' => $cats,
            'action' => route('houses.update', $house),
            'method' => 'PUT',
            'panel' => 'admin',
        ]);
    }

    public function update(HouseRequest $request, House $house, HouseDataService $service)
    {
        $validated = $request->validated();
        $submit = $request->boolean('submit');

        $service->save($house, $validated, auth()->id(), $submit);

        if ($request->hasFile('photo_front') || $request->hasFile('photo_angle') || $request->hasFile('photo_side') || $request->hasFile('photo_back') || $request->hasFile('photo_family_room') || $request->hasFile('photo_bathroom')) {
            $service->storePhotos($house, $request->allFiles(), auth()->id());
        }

        return redirect()
            ->route('houses.show', $house)
            ->with('success', $submit ? 'Data RTLH berhasil diperbarui.' : 'Draft data RTLH berhasil diperbarui.');
    }

    public function destroy(House $house)
    {
        Gate::authorize('delete', $house);

        $house->delete();

        return redirect()
            ->route('houses.index')
            ->with('success', 'Data RTLH berhasil dihapus.');
    }

    public function show(House $house)
    {
        $house->load([
            'region',
            'assessments.items',
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'floor.material',
            'floor.condition',
            'wall.material',
            'wall.condition',
            'roof.frameMaterial',
            'roof.frameCondition',
            'roof.material',
            'roof.condition',
            'sanitation.waterSource',
            'sanitation.wastewaterDisposal',
            'sanitation.toiletType',
            'sanitation.fecalDisposalType',
            'utility.lightingSource',
            'occupants',
            'photos',
        ]);

        $assessmentCategories = MasterCategory::with([
            'values' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ])
        ->where('is_active', true)
        ->orderBy('id')
        ->get();

        return view('houses.show', compact(
            'house',
            'assessmentCategories'
        ));
    }
}