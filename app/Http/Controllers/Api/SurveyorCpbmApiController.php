<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CpbmRequest;
use App\Models\Cpbm;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SurveyorCpbmApiController extends Controller
{
    /**
     * Cari data CPBM untuk mobile surveyor.
     * GET /api/v1/surveyor/cpbm/search?q={query}
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = trim($request->string('q')->toString());

        if (strlen($keyword) < 1) {
            $cpbms = Cpbm::with(['district', 'region'])
                ->withExists('houses')
                ->latest('id')
                ->limit(10)
                ->get();
        } else {
            $cpbms = Cpbm::with(['district', 'region'])
                ->withExists('houses')
                ->search($keyword)
                ->limit(15)
                ->get();
        }

        $results = $cpbms->map(fn (Cpbm $cpbm) => $this->formatCpbm($cpbm));

        return response()->json($results);
    }

    /**
     * Tambah data CPBM baru dari aplikasi mobile surveyor.
     * POST /api/v1/surveyor/cpbm
     */
    public function store(CpbmRequest $request): JsonResponse
    {
        $validated = $request->validated();

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

        return response()->json([
            'message' => 'Data CPBM berhasil ditambahkan.',
            'data'    => $this->formatCpbm($cpbm),
        ], 201);
    }

    /**
     * Format data CPBM seragam untuk response API mobile.
     */
    private function formatCpbm(Cpbm $cpbm): array
    {
        return [
            'id'            => $cpbm->id,
            'nik'           => $cpbm->nik,
            'name'          => $cpbm->name,
            'phone'         => $cpbm->phone,
            'address'       => $cpbm->address,
            'rt'            => $cpbm->rt,
            'rw'            => $cpbm->rw,
            'district_id'   => $cpbm->district_id,
            'district_name' => $cpbm->district?->name,
            'region_id'     => $cpbm->region_id,
            'village_name'  => $cpbm->region?->name,
            'full_address'  => $cpbm->full_address,
            'notes'         => $cpbm->notes,
            'has_house'     => (bool) ($cpbm->houses_exists ?? $cpbm->houses()->exists()),
        ];
    }
}
