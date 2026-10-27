<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Region;
use Illuminate\Http\Request;

class HouseMapController extends Controller
{
    /**
     * Tampilkan halaman Peta WebGIS Sebaran & Realisasi RTLH untuk Petugas (Admin & Surveyor).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $baseScope = House::query();
        if (!$user->hasRole('admin') && $request->boolean('mine')) {
            $baseScope->where('created_by', $user->id);
        }

        // Hitung total titik yang memiliki data koordinat
        $totalCoordinates = (clone $baseScope)
            ->where(function ($q) {
                $q->whereNotNull('location')
                    ->orWhereNotNull('latitude');
            })
            ->count();

        // Hitung status realisasi
        $realizedCount = (clone $baseScope)
            ->where('realization_status', 'realized')
            ->count();

        $notRealizedCount = (clone $baseScope)
            ->where(function ($q) {
                $q->where('realization_status', 'not_realized')
                    ->orWhereNull('realization_status');
            })
            ->count();

        // Ambil daftar kecamatan dan kelurahan di Kota Cirebon
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

        return view('houses.map', compact(
            'totalCoordinates',
            'realizedCount',
            'notRealizedCount',
            'districts',
            'villages'
        ));
    }
}
