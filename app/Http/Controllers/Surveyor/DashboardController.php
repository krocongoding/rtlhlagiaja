<?php

namespace App\Http\Controllers\Surveyor;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Region;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $isAdmin = auth()->user()->hasRole('admin');

        $query = House::query();
        if (! $isAdmin) {
            $query->where('created_by', $userId);
        }

        $latestDraft = (clone $query)
            ->where('status', 'draft')
            ->latest('updated_at')
            ->first();

        $recentSubmissions = (clone $query)
            ->with('region')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $realizedCount = (clone $query)
            ->where('realization_status', 'realized')
            ->count();

        $notRealizedCount = (clone $query)
            ->where(function ($q) {
                $q->where('realization_status', '!=', 'realized')
                    ->orWhereNull('realization_status');
            })
            ->count();

        // Breakdown realisasi per kelurahan binaan surveyor
        $regionStats = Region::whereHas('houses', function ($q) use ($userId, $isAdmin) {
            if (! $isAdmin) {
                $q->where('created_by', $userId);
            }
        })
        ->withCount([
            'houses as total_houses' => function ($q) use ($userId, $isAdmin) {
                if (! $isAdmin) {
                    $q->where('created_by', $userId);
                }
            },
            'houses as realized_houses' => function ($q) use ($userId, $isAdmin) {
                $q->where('realization_status', 'realized');
                if (! $isAdmin) {
                    $q->where('created_by', $userId);
                }
            },
            'houses as not_realized_houses' => function ($q) use ($userId, $isAdmin) {
                $q->where(function ($sq) {
                    $sq->where('realization_status', '!=', 'realized')
                        ->orWhereNull('realization_status');
                });
                if (! $isAdmin) {
                    $q->where('created_by', $userId);
                }
            },
        ])
        ->orderByDesc('total_houses')
        ->take(6)
        ->get();

        return view('surveyor.dashboard', [
            'mine' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'submitted' => (clone $query)->where('status', 'submitted')->count(),
            'revision' => (clone $query)->where('status', 'revision')->count(),
            'verified' => (clone $query)->where('status', 'verified')->count(),
            'published' => (clone $query)->where('status', 'published')->count(),
            'realizedCount' => $realizedCount,
            'notRealizedCount' => $notRealizedCount,
            'regionStats' => $regionStats,
            'latestDraft' => $latestDraft,
            'recentSubmissions' => $recentSubmissions,
        ]);
    }
}