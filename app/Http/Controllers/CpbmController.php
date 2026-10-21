<?php

namespace App\Http\Controllers;

use App\Http\Requests\CpbmRequest;
use App\Models\Cpbm;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CpbmController extends Controller
{
    /**
     * Tampilkan daftar basis data CPBM.
     */
    public function index(Request $request)
    {
        $query = Cpbm::with(['district', 'region', 'houses'])
            ->latest('id');

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled('survey_status')) {
            $status = $request->string('survey_status')->toString();
            if ($status === 'sudah') {
                $query->has('houses');
            } elseif ($status === 'belum') {
                $query->doesntHave('houses');
            }
        }

        $cpbms = $query->paginate(15)->withQueryString();

        $totalCpbm = Cpbm::count();
        $sudahSurveiCount = Cpbm::has('houses')->count();
        $belumSurveiCount = Cpbm::doesntHave('houses')->count();

        $districts = Region::kotaCirebon()
            ->whereIn('type', ['district', 'kecamatan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $villages = Region::kotaCirebon()
            ->whereIn('type', ['village', 'desa'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cpbm.index', compact(
            'cpbms',
            'totalCpbm',
            'sudahSurveiCount',
            'belumSurveiCount',
            'districts',
            'villages'
        ));
    }

    /**
     * Halaman form tambah CPBM baru.
     */
    public function create()
    {
        $districts = Region::kotaCirebon()
            ->whereIn('type', ['district', 'kecamatan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $villages = Region::kotaCirebon()
            ->whereIn('type', ['village', 'desa'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cpbm.create', [
            'cpbm' => new Cpbm(),
            'districts' => $districts,
            'villages' => $villages,
        ]);
    }

    /**
     * Simpan data CPBM baru.
     */
    public function store(CpbmRequest $request)
    {
        $validated = $request->validated();

        // Otomatis tentukan district_id jika kelurahan dipilih
        if (empty($validated['district_id']) && !empty($validated['region_id'])) {
            $village = Region::find($validated['region_id']);
            if ($village && $village->parent_id) {
                $validated['district_id'] = $village->parent_id;
            }
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $cpbm = Cpbm::create($validated);
        $cpbm->load(['district', 'region']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data CPBM berhasil disimpan.',
                'cpbm' => [
                    'id' => $cpbm->id,
                    'nik' => $cpbm->nik,
                    'name' => $cpbm->name,
                    'phone' => $cpbm->phone,
                    'address' => $cpbm->address,
                    'rt' => $cpbm->rt,
                    'rw' => $cpbm->rw,
                    'district_name' => $cpbm->district?->name,
                    'village_name' => $cpbm->region?->name,
                    'region_id' => $cpbm->region_id,
                    'full_address' => $cpbm->full_address,
                ],
            ], 201);
        }

        return redirect()
            ->route('cpbm.index')
            ->with('ok', 'Data CPBM berhasil ditambahkan.');
    }

    /**
     * Detail CPBM dan daftar survei yang terhubung.
     */
    public function show(Cpbm $cpbm)
    {
        $cpbm->load(['district', 'region', 'creator', 'houses.region', 'houses.latestAssessment']);

        return view('cpbm.show', compact('cpbm'));
    }

    /**
     * Halaman edit data CPBM.
     */
    public function edit(Cpbm $cpbm)
    {
        $districts = Region::kotaCirebon()
            ->whereIn('type', ['district', 'kecamatan'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $villages = Region::kotaCirebon()
            ->whereIn('type', ['village', 'desa'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('cpbm.edit', compact('cpbm', 'districts', 'villages'));
    }

    /**
     * Perbarui data CPBM.
     */
    public function update(CpbmRequest $request, Cpbm $cpbm)
    {
        $validated = $request->validated();

        if (empty($validated['district_id']) && !empty($validated['region_id'])) {
            $village = Region::find($validated['region_id']);
            if ($village && $village->parent_id) {
                $validated['district_id'] = $village->parent_id;
            }
        }

        $validated['updated_by'] = auth()->id();
        $cpbm->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data CPBM berhasil diperbarui.',
                'cpbm' => $cpbm,
            ]);
        }

        return redirect()
            ->route('cpbm.index')
            ->with('ok', 'Data CPBM berhasil diperbarui.');
    }

    /**
     * Hapus data CPBM.
     */
    public function destroy(Cpbm $cpbm)
    {
        if ($cpbm->houses()->exists()) {
            return back()->with('error', 'Data CPBM ini telah terhubung dengan data rumah RTLH dan tidak dapat dihapus langsung.');
        }

        $cpbm->delete();

        return redirect()
            ->route('cpbm.index')
            ->with('ok', 'Data CPBM berhasil dihapus.');
    }

    /**
     * Endpoint API JSON untuk pencarian langsung CPBM (autocomplete) pada form rumah.
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = trim($request->string('q')->toString());

        if (strlen($keyword) < 1) {
            return response()->json([]);
        }

        $results = Cpbm::with(['district', 'region'])
            ->search($keyword)
            ->limit(15)
            ->get()
            ->map(function (Cpbm $cpbm) {
                return [
                    'id' => $cpbm->id,
                    'nik' => $cpbm->nik,
                    'name' => $cpbm->name,
                    'phone' => $cpbm->phone,
                    'address' => $cpbm->address,
                    'rt' => $cpbm->rt,
                    'rw' => $cpbm->rw,
                    'district_name' => $cpbm->district?->name,
                    'village_name' => $cpbm->region?->name,
                    'region_id' => $cpbm->region_id,
                    'full_address' => $cpbm->full_address,
                    'has_house' => $cpbm->houses()->exists(),
                ];
            });

        return response()->json($results);
    }
}
