<?php

namespace App\Providers;

use App\Models\House;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'components.layouts.app'], function ($view) {
            $user = auth()->user();
            if (!$user) {
                $view->with([
                    'pendingRevisionCount' => 0,
                    'revisionNotifications' => collect(),
                ]);
                return;
            }

            // Notifikasi revisi hanya ditujukan langsung kepada user/surveyor pemilik data yang direvisi
            $query = House::query()
                ->where('status', 'revision')
                ->where('created_by', $user->id)
                ->with(['region', 'reviser', 'cpbm']);

            $pendingRevisionCount = (clone $query)->count();
            $revisionNotifications = $query->latest('revised_at')->latest('updated_at')->take(5)->get();

            $view->with(compact('pendingRevisionCount', 'revisionNotifications'));
        });
    }
}
