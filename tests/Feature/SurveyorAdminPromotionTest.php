<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $this->surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'superadmin-test@cirebon-rtlh.test'],
        [
            'name' => 'Super Administrator',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($this->adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-promo-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Lapangan Handal',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->surveyor->hasRole('surveyor')) {
        $this->surveyor->roles()->attach($this->surveyorRole);
    }
});

test('admin can promote a surveyor to have administrator role with 1-click toggle', function () {
    expect($this->surveyor->hasRole('admin'))->toBeFalse();

    $response = $this->actingAs($this->admin)
        ->from(route('admin.surveyors.show', $this->surveyor))
        ->post(route('admin.surveyors.toggle-admin', $this->surveyor));

    $response->assertRedirect(route('admin.surveyors.show', $this->surveyor));
    $response->assertSessionHas('ok');

    // Verifikasi surveyor kini berstatus admin
    expect($this->surveyor->fresh()->hasRole('admin'))->toBeTrue();
    // Role surveyor tetap dipertahankan (multi-role)
    expect($this->surveyor->fresh()->hasRole('surveyor'))->toBeTrue();

    // Verifikasi surveyor yang dipromosikan sekarang bisa masuk ke Command Center Admin
    $dashboardResponse = $this->actingAs($this->surveyor->fresh())->get(route('admin.dashboard'));
    $dashboardResponse->assertOk();
    $dashboardResponse->assertSee('Admin Command Center');
});

test('admin can demote an admin-promoted surveyor back to regular surveyor', function () {
    // Berikan peran admin terlebih dahulu
    $this->surveyor->roles()->syncWithoutDetaching([$this->adminRole->id]);
    expect($this->surveyor->fresh()->hasRole('admin'))->toBeTrue();

    // Cabut hak admin
    $response = $this->actingAs($this->admin)
        ->from(route('admin.surveyors.show', $this->surveyor))
        ->post(route('admin.surveyors.toggle-admin', $this->surveyor));

    $response->assertRedirect(route('admin.surveyors.show', $this->surveyor));
    $response->assertSessionHas('ok');

    // Verifikasi peran admin sudah dicabut dan kembali jadi surveyor biasa
    expect($this->surveyor->fresh()->hasRole('admin'))->toBeFalse();
    expect($this->surveyor->fresh()->hasRole('surveyor'))->toBeTrue();

    // Verifikasi akses ke admin dashboard ditolak
    $dashboardResponse = $this->actingAs($this->surveyor->fresh())->get(route('admin.dashboard'));
    $dashboardResponse->assertForbidden();
});

test('admin cannot demote their own account to prevent lockout', function () {
    $response = $this->actingAs($this->admin)
        ->from(route('admin.surveyors.show', $this->admin))
        ->post(route('admin.surveyors.toggle-admin', $this->admin));

    $response->assertSessionHas('error');
    expect($this->admin->fresh()->hasRole('admin'))->toBeTrue();
});

test('regular surveyor without admin role cannot promote anyone', function () {
    $otherSurveyor = User::factory()->create(['is_active' => true]);
    $otherSurveyor->roles()->attach($this->surveyorRole);

    $response = $this->actingAs($this->surveyor)
        ->post(route('admin.surveyors.toggle-admin', $otherSurveyor));

    $response->assertForbidden();
    expect($otherSurveyor->fresh()->hasRole('admin'))->toBeFalse();
});

test('admin can promote a surveyor via the edit form checkbox', function () {
    expect($this->surveyor->hasRole('admin'))->toBeFalse();

    $response = $this->actingAs($this->admin)
        ->put(route('admin.surveyors.update', $this->surveyor), [
            'name' => 'Surveyor Lapangan Updated',
            'email' => $this->surveyor->email,
            'is_active' => 1,
            'role_settings_submitted' => 1,
            'is_admin' => 1,
        ]);

    $response->assertRedirect(route('admin.surveyors.show', $this->surveyor));
    expect($this->surveyor->fresh()->hasRole('admin'))->toBeTrue();
});
