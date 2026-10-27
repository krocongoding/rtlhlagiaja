<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Services\AuditLogger;
use App\Services\HouseDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HouseRealizationController extends Controller
{
    /**
     * Memperbarui status dan data realisasi RTLH serta dokumentasi foto pasca-bantuan (Sesudah).
     * Hanya Admin atau Surveyor pemilik data yang diizinkan mengakses.
     */
    public function update(Request $request, House $house, HouseDataService $service)
    {
        Gate::authorize('update', $house);

        $validated = $request->validate([
            'realization_status' => ['required', 'string', 'in:realized,not_realized'],
            'funding_source' => ['nullable', 'string', 'max:100'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'realized_at' => ['nullable', 'date'],
            'realized_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'realization_notes' => ['nullable', 'string', 'max:1000'],
            'photo_after_front' => ['nullable', 'image', 'max:5120'],
            'photo_after_side' => ['nullable', 'image', 'max:5120'],
            'photo_after_family_room' => ['nullable', 'image', 'max:5120'],
            'photo_after_bathroom' => ['nullable', 'image', 'max:5120'],
        ], [
            'realization_status.required' => 'Status realisasi wajib dipilih.',
            'realization_status.in' => 'Status realisasi harus Sudah Terealisasi atau Belum Terealisasi.',
            'budget_amount.numeric' => 'Nominal anggaran harus berupa angka yang valid.',
            'budget_amount.min' => 'Nominal anggaran tidak boleh bernilai negatif.',
        ]);

        $old = $house->toArray();

        $updateData = [
            'realization_status' => $validated['realization_status'],
            'funding_source' => $validated['funding_source'] ?? $house->funding_source,
            'budget_amount' => (array_key_exists('budget_amount', $validated) && $validated['budget_amount'] !== null && $validated['budget_amount'] !== '')
                ? $validated['budget_amount']
                : $house->budget_amount,
            'realization_notes' => $validated['realization_notes'] ?? null,
            'updated_by' => auth()->id(),
        ];

        if ($validated['realization_status'] === 'realized') {
            $updateData['realized_at'] = !empty($validated['realized_at'])
                ? $validated['realized_at']
                : ($house->realized_at ?: now());
            $updateData['realized_by'] = auth()->id();
            $updateData['realized_score'] = array_key_exists('realized_score', $validated) && $validated['realized_score'] !== null && $validated['realized_score'] !== ''
                ? (int) $validated['realized_score']
                : ($house->realized_score ?? 0);
        } else {
            $updateData['realized_at'] = null;
            $updateData['realized_by'] = null;
            $updateData['realized_score'] = null;
        }

        $house->update($updateData);

        // Simpan foto dokumentasi sesudah bantuan jika diunggah
        if ($request->hasFile('photo_after_front') || $request->hasFile('photo_after_side') || $request->hasFile('photo_after_family_room') || $request->hasFile('photo_after_bathroom')) {
            $service->storeRealizationPhotos($house, $request->allFiles(), auth()->id());
        }

        AuditLogger::log(
            $request->user(),
            'REALIZATION_UPDATE',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        $statusLabel = $validated['realization_status'] === 'realized'
            ? 'Sudah Terealisasi'
            : 'Belum Terealisasi';

        return back()->with(
            'ok',
            "Status realisasi rumah ({$house->house_code}) berhasil diperbarui menjadi {$statusLabel}."
        );
    }
}
