<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = createAdminUser('admin-map-test@cirebon-rtlh.test', 'Admin Map Test');
    $this->surveyor = createSurveyorUser('surveyor-map-test@cirebon-rtlh.test', 'Surveyor Map Test');
    $this->surveyorOther = createSurveyorUser('surveyor-other-map@cirebon-rtlh.test', 'Surveyor Other Map Test');
    $this->region = getTestVillage();
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

    // Notifikasi HANYA dikirim langsung ke user yang direvisi:
    // 1. Admin yang mereview tidak mendapatkan lonceng notifikasi revisi
    $adminResponse = $this->actingAs($this->admin)->get(route('admin.dashboard'));
    $adminResponse->assertOk();
    $adminResponse->assertDontSee('gov-notif-badge');

    // 2. Surveyor lain yang bukan pembuat data juga tidak mendapatkan lonceng notifikasi
    $otherSurveyorResponse = $this->actingAs($this->surveyorOther)->get(route('surveyor.dashboard'));
    $otherSurveyorResponse->assertOk();
    $otherSurveyorResponse->assertDontSee('gov-notif-badge');
});

test('officers can view all inputted houses on map without publish status while public viewers only view published', function () {
    // 1. Rumah draft belum dipublish (tapi sudah diinput koordinatnya)
    $draftHouse = House::create([
        'house_code' => 'MAP-DRAFT-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990055',
        'head_of_household_name' => 'Warga Input Draft',
        'survey_year' => 2026,
        'status' => 'draft',
        'is_public' => false,
        'realization_status' => 'not_realized',
        'latitude' => -6.7110,
        'longitude' => 108.5510,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // 2. Rumah belum terealisasi tapi sudah dipublish oleh admin
    $publishedUnrealizedHouse = House::create([
        'house_code' => 'MAP-PUB-UNREAL-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990056',
        'head_of_household_name' => 'Warga Publik Belum Realisasi',
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'realization_status' => 'not_realized',
        'latitude' => -6.7120,
        'longitude' => 108.5520,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Admin dapat melihat semua rumah yang sudah diinput di peta tanpa harus dipublish
    $adminRes = $this->actingAs($this->admin)->getJson('/api/v1/public/rtlh');
    $adminRes->assertOk();
    $adminCodes = collect($adminRes->json('data'))->pluck('house_code');
    expect($adminCodes)->toContain('MAP-DRAFT-01');
    expect($adminCodes)->toContain('MAP-PUB-UNREAL-01');

    // Surveyor juga dapat melihat data rumah yang sudah diinput di sebaran peta
    $surveyorRes = $this->actingAs($this->surveyor)->getJson('/api/v1/public/rtlh');
    $surveyorRes->assertOk();
    $surveyorCodes = collect($surveyorRes->json('data'))->pluck('house_code');
    expect($surveyorCodes)->toContain('MAP-DRAFT-01');
    expect($surveyorCodes)->toContain('MAP-PUB-UNREAL-01');

    // Viewer publik tanpa login:
    // - BISA melihat rumah yang belum terealisasi jika SUDAH dipublish
    // - TIDAK BISA melihat rumah draft yang belum dipublish
    $this->app['auth']->forgetGuards();
    $guestRes = $this->getJson('/api/v1/public/rtlh');
    $guestRes->assertOk();
    $guestCodes = collect($guestRes->json('data'))->pluck('house_code');
    expect($guestCodes)->toContain('MAP-PUB-UNREAL-01');
    expect($guestCodes)->not->toContain('MAP-DRAFT-01');
});
