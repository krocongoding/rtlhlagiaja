<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\House;
use App\Models\Region;

class DashboardController extends Controller
{
    public function index()
    {
        $realizedCount = House::where('realization_status', 'realized')->count();
        $notRealizedCount = House::where(function ($q) {
            $q->where('realization_status', '!=', 'realized')
              ->orWhereNull('realization_status');
        })->count();

        // Top 6 kelurahan dengan unit RTLH terbanyak
        $topRegions = Region::has('houses')
            ->withCount('houses')
            ->orderByDesc('houses_count')
            ->take(6)
            ->get();

        // 6 Aktivitas audit log terbaru
        $recentActivities = AuditLog::with('user')
            ->latest()
            ->take(6)
            ->get();

        // 5 Antrian review paling mendesak (status submitted, yang paling lama belum direview)
        $urgentReviews = House::with(['region', 'creator'])
            ->where('status', 'submitted')
            ->oldest('updated_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'total' => House::count(),
            'draft' => House::where('status', 'draft')->count(),
            'submitted' => House::where('status', 'submitted')->count(),
            'verified' => House::where('status', 'verified')->count(),
            'published' => House::where('status', 'published')->count(),
            'revision' => House::where('status', 'revision')->count(),
            'public' => House::where('is_public', true)->count(),
            'realizedCount' => $realizedCount,
            'notRealizedCount' => $notRealizedCount,
            'topRegions' => $topRegions,
            'recentActivities' => $recentActivities,
            'urgentReviews' => $urgentReviews,
        ]);
    }
}   