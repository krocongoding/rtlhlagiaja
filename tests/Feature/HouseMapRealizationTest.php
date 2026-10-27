<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-map-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin Map Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-map-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Map Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->surveyor->hasRole('surveyor')) {
        $this->surveyor->roles()->attach($surveyorRole);
    }

    $this->surveyorOther = User::firstOrCreate(
        ['email' => 'surveyor-other-map@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Other Map Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->surveyorOther->hasRole('surveyor')) {
        $this->surveyorOther->roles()->attach($surveyorRole);
    }

    $this->region = Region::firstOrCreate(
        ['code' => '32.74.01.1001'],
        [
            'name' => 'Kesenden',
            'type' => 'village',
            'level' => 2,
            'is_active' => true,
        ]
    );
});

test('unauthenticated guest cannot access officer map and is redirected to login', function () {
    $response = $this->get(route('houses.map'));
    $response->assertRedirect(route('login'));
});

test('admin can access officer map and see realization statistics widgets', function () {
    $response = $this->actingAs($this->admin)->get(route('houses.map'));

    $response->assertOk();
    $response->assertSee('Peta Sebaran');
    $response->assertSee('Sudah Terealisasi');
    $response->assertSee('Belum Terealisasi');
    $response->assertSee('Capaian Realisasi');
    $response->assertSee('id="map"', false);
    $response->assertSee('id="realization_status"', false);
});

test('surveyor can access officer map and see mine toggle filter', function () {
    $response = $this->actingAs($this->surveyor)->get(route('houses.map'));

    $response->assertOk();
    $response->assertSee('Peta Sebaran');
    $response->assertSee('Hanya Data Saya');
});

test('api returns correct realization status for realized and not realized houses', function () {
    $realizedHouse = House::create([
        'house_code' => 'MAP-REAL-TEST-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990011',
        'head_of_household_name' => 'Warga Sudah Realisasi',
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'realization_status' => 'realized',
        'funding_source' => 'APBD Kota Cirebon',
        'budget_amount' => 20000000,
        'latitude' => -6.7050,
        'longitude' => 108.5550,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $notRealizedHouse = House::create([
        'house_code' => 'MAP-UNREAL-TEST-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990012',
        'head_of_household_name' => 'Warga Belum Realisasi',
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'realization_status' => 'not_realized',
        'latitude' => -6.7060,
        'longitude' => 108.5560,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Test filter realized
    $resReal = $this->actingAs($this->admin)->getJson('/api/v1/public/rtlh?realization_status=realized');
    $resReal->assertOk();
    $dataReal = collect($resReal->json('data'));
    expect($dataReal->pluck('house_code'))->toContain('MAP-REAL-TEST-01');
    expect($dataReal->pluck('house_code'))->not->toContain('MAP-UNREAL-TEST-01');

    // Test filter not_realized
    $resNotReal = $this->actingAs($this->admin)->getJson('/api/v1/public/rtlh?realization_status=not_realized');
    $resNotReal->assertOk();
    $dataNotReal = collect($resNotReal->json('data'));
    expect($dataNotReal->pluck('house_code'))->toContain('MAP-UNREAL-TEST-01');
    expect($dataNotReal->pluck('house_code'))->not->toContain('MAP-REAL-TEST-01');
});

test('surveyor can filter map data to only their own surveyed houses with mine parameter', function () {
    $houseMine = House::create([
        'house_code' => 'MINE-MAP-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990021',
        'head_of_household_name' => 'Warga Milik Saya',
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'latitude' => -6.7070,
        'longitude' => 108.5570,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $houseOther = House::create([
        'house_code' => 'OTHER-MAP-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990022',
        'head_of_household_name' => 'Warga Surveyor Lain',
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'latitude' => -6.7080,
        'longitude' => 108.5580,
        'created_by' => $this->surveyorOther->id,
        'updated_by' => $this->surveyorOther->id,
    ]);

    $res = $this->actingAs($this->surveyor)->getJson('/api/v1/public/rtlh?mine=1');
    $res->assertOk();
    $codes = collect($res->json('data'))->pluck('house_code');

    expect($codes)->toContain('MINE-MAP-01');
    expect($codes)->not->toContain('OTHER-MAP-01');
});

test('topbar notification bell renders revision badge when user has houses in revision', function () {
    $notes = 'Perbaiki foto fasad depan';

    $houseRev = House::create([
        'house_code' => 'REV-BELL-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990031',
        'head_of_household_name' => 'Warga Revisi Bell',
        'survey_year' => 2026,
        'status' => 'revision',
        'revision_notes' => $notes,
        'revised_at' => now(),
        'revised_by' => $this->admin->id,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->surveyor)->get(route('surveyor.dashboard'));
    $response->assertOk();
    $response->assertSee('notifDropdownBtn');
    $response->assertSee('gov-notif-badge');
    $response->assertSee('REV-BELL-01');
    $response->assertSee($notes);
});
