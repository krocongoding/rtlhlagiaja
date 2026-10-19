<?php

namespace App\Http\Controllers\Surveyor;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseRequest;
use App\Models\House;
use App\Models\MasterCategory;
use App\Models\Region;
use App\Services\AuditLogger;
use App\Services\HouseDataService;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    /**
     * Data milik Surveyor.
     *
     * Admin boleh melihat seluruh data.
     */
    public function index(Request $request)
    {
        $query = House::with('region')
            ->latest('id');

        if (!auth()->user()->hasRole('admin')) {
            $query->where(
                'created_by',
                auth()->id()
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->input('search')
            );

            $query->where(function ($q) use ($search) {
                $q->where(
                    'house_code',
                    'ilike',
                    "%{$search}%"
                )->orWhere(
                    'address',
                    'ilike',
                    "%{$search}%"
                );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
        }

        $houses = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'houses.index',
            [
                'houses' => $houses,
                'panel' => 'surveyor',
            ]
        );
    }

    /**
     * Form tambah data.
     */
    public function create(Request $request)
    {
        $cats = MasterCategory::with([
            'values' => function ($query) {
                $query
                    ->where('is_active', true)
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

        return view(
            'houses.form',
            [
                'house' => $house,

                'regions' => $regions,

                'cats' => $cats,

                'action' => route(
                    'surveyor.rtlh.store'
                ),

                'method' => 'POST',

                'panel' => 'surveyor',
            ]
        );
    }

    /**
     * Simpan data baru.
     *
     * submit=0 -> draft
     * submit=1 -> submitted
     */
    public function store(
        HouseRequest $request,
        HouseDataService $service
    ) {
        $validated = $request->validated();

        $submit = $request->boolean('submit');

        $house = House::create([
            ...$validated,

            'house_code' => $service->generateUniqueCode(
                $validated['region_id']
            ),

            'created_by' => auth()->id(),

            'updated_by' => auth()->id(),

            'status' => 'draft',

            'is_public' => false,
        ]);

        $service->save(
            $house,
            $validated,
            auth()->id(),
            $submit
        );

        $service->storePhotos(
            $house,
            $request->allFiles(),
            auth()->id()
        );

        AuditLogger::log(
            $request->user(),
            $submit ? 'SUBMIT' : 'CREATE',
            'House',
            $house->id,
            null,
            $house->fresh()->toArray()
        );

        return redirect()
            ->route(
                'surveyor.rtlh.show',
                $house
            )
            ->with(
                'ok',
                $submit
                    ? 'Data berhasil dikirim ke Admin untuk review.'
                    : 'Data berhasil disimpan sebagai draft.'
            );
    }

    /**
     * Detail.
     *
     * Admin bisa melihat semua.
     * Surveyor hanya data miliknya.
     */
    public function show(House $house)
    {
        $this->authorizeAccess($house);

        $house->load([
            'region',
            'structure',
            'floor',
            'ceiling',
            'wall',
            'roof',
            'sanitation',
            'utility',
            'assessments.items',
            'photos',
        ]);

        return view(
            'houses.show',
            [
                'house' => $house,
                'panel' => 'surveyor',
            ]
        );
    }

    /**
     * Edit.
     *
     * Hanya Surveyor pemilik data.
     */
    public function edit(House $house)
    {
        abort_unless(
            $house->created_by === auth()->id(),
            403
        );

        $cats = MasterCategory::with([
            'values' => function ($query) {
                $query
                    ->where('is_active', true)
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

        $house->load([
            'cpbm',
            'structure',
            'floor',
            'ceiling',
            'wall',
            'roof',
            'sanitation',
            'utility',
        ]);

        return view(
            'houses.form',
            [
                'house' => $house,

                'regions' => $regions,

                'cats' => $cats,

                'action' => route(
                    'surveyor.rtlh.update',
                    $house
                ),

                'method' => 'PUT',

                'panel' => 'surveyor',
            ]
        );
    }

    /**
     * Update.
     *
     * submit=0 -> tetap status sebelumnya.
     * submit=1 -> submitted.
     */
    public function update(
        HouseRequest $request,
        House $house,
        HouseDataService $service
    ) {
        abort_unless(
            $house->created_by === auth()->id(),
            403
        );

        $validated = $request->validated();

        $submit = $request->boolean('submit');

        $old = $house->toArray();

        $service->save(
            $house,
            $validated,
            auth()->id(),
            $submit
        );

        $service->storePhotos(
            $house,
            $request->allFiles(),
            auth()->id()
        );

        AuditLogger::log(
            $request->user(),
            $submit ? 'SUBMIT' : 'UPDATE',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return redirect()
            ->route(
                'surveyor.rtlh.show',
                $house
            )
            ->with(
                'ok',
                $submit
                    ? 'Data berhasil dikirim ke Admin untuk review.'
                    : 'Data berhasil diperbarui.'
            );
    }

    /**
     * Akses detail:
     * Admin -> semua
     * Surveyor -> miliknya sendiri
     */
    private function authorizeAccess(House $house): void
    {
        if (auth()->user()->hasRole('admin')) {
            return;
        }

        abort_unless(
            $house->created_by === auth()->id(),
            403
        );
    }
}
