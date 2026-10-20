<?php

use App\Models\AuditLog;
use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-realization-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin Realisasi',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-realization-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Realisasi',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->surveyor->hasRole('surveyor')) {
        $this->surveyor->roles()->attach($surveyorRole);
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

test('admin can mark a house as realized with budget and notes', function () {
    $house = House::create([
        'house_code' => 'RTLH-REAL-001',
        'region_id' => $this->region->id,
        'nik' => '3274010101880001',
        'head_of_household_name' => 'Pak Ahmad',
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'not_realized',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('houses.realization.update', $house), [
            'realization_status' => 'realized',
            'funding_source' => 'APBD Kabupaten Cirebon',
            'budget_amount' => 20000000,
            'realized_at' => '2026-10-15',
            'realization_notes' => 'Pembangunan selesai 100% dan telah diserahterimakan.',
        ]);

    $response->assertSessionHas('ok');

    $house->refresh();

    expect($house->realization_status)->toBe('realized');
    expect((float)$house->budget_amount)->toBe(20000000.0);
    expect($house->funding_source)->toBe('APBD Kabupaten Cirebon');
    expect($house->realized_at->format('Y-m-d'))->toBe('2026-10-15');
    expect($house->realized_by)->toBe($this->admin->id);
    expect($house->realizer->id)->toBe($this->admin->id);
    expect($house->realization_notes)->toBe('Pembangunan selesai 100% dan telah diserahterimakan.');

    expect(AuditLog::where('action', 'REALIZATION_UPDATE')->where('auditable_id', $house->id)->exists())->toBeTrue();
});

test('surveyor can also mark a house as realized', function () {
    $house = House::create([
        'house_code' => 'RTLH-REAL-002',
        'region_id' => $this->region->id,
        'nik' => '3274010101880002',
        'head_of_household_name' => 'Ibu Siti',
        'survey_year' => 2026,
        'status' => 'verified',
        'realization_status' => 'not_realized',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->surveyor)
        ->post(route('houses.realization.update', $house), [
            'realization_status' => 'realized',
            'funding_source' => 'BAZNAS',
            'budget_amount' => 15000000,
            'realization_notes' => 'Monev lapangan: atap dan lantai selesai direhabilitasi.',
        ]);

    $response->assertSessionHas('ok');

    $house->refresh();

    expect($house->realization_status)->toBe('realized');
    expect($house->realized_by)->toBe($this->surveyor->id);
    expect($house->realized_at)->not->toBeNull();
});

test('user can revert realization status back to not_realized', function () {
    $house = House::create([
        'house_code' => 'RTLH-REAL-003',
        'region_id' => $this->region->id,
        'nik' => '3274010101880003',
        'head_of_household_name' => 'Pak Dedi',
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'realized',
        'realized_at' => now(),
        'realized_by' => $this->admin->id,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('houses.realization.update', $house), [
            'realization_status' => 'not_realized',
            'realization_notes' => 'Dibatalkan karena revisi alokasi dana.',
        ]);

    $response->assertSessionHas('ok');

    $house->refresh();

    expect($house->realization_status)->toBe('not_realized');
    expect($house->realized_at)->toBeNull();
    expect($house->realized_by)->toBeNull();
    expect($house->realization_notes)->toBe('Dibatalkan karena revisi alokasi dana.');
});

test('realization status requires valid value and validation rules', function () {
    $house = House::create([
        'house_code' => 'RTLH-REAL-004',
        'region_id' => $this->region->id,
        'nik' => '3274010101880004',
        'head_of_household_name' => 'Warga Validasi',
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'not_realized',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->post(route('houses.realization.update', $house), [
            'realization_status' => 'invalid_status',
            'budget_amount' => -5000,
        ]);

    $response->assertSessionHasErrors(['realization_status', 'budget_amount']);
});

test('unauthenticated guest cannot update realization status', function () {
    $house = House::create([
        'house_code' => 'RTLH-REAL-005',
        'region_id' => $this->region->id,
        'nik' => '3274010101880005',
        'head_of_household_name' => 'Warga Tamu',
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'not_realized',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->post(route('houses.realization.update', $house), [
        'realization_status' => 'realized',
    ]);

    $response->assertRedirect(route('login'));
});

test('houses index can be rendered for admin and surveyor without 500 error', function () {
    $responseAdmin = $this->actingAs($this->admin)->get(route('houses.index'));
    $responseAdmin->assertOk();
    $responseAdmin->assertSee('Daftar Unit Rumah RTLH');

    $responseSurveyor = $this->actingAs($this->surveyor)->get(route('houses.index'));
    $responseSurveyor->assertOk();
    $responseSurveyor->assertSee('Daftar Unit Rumah RTLH');
});

test('houses show renders geospatial mini map and leaflet assets for admin and surveyor', function () {
    $house = House::create([
        'house_code' => 'RTLH-MAP-001',
        'region_id' => $this->region->id,
        'nik' => '3274010101770001',
        'head_of_household_name' => 'Warga Peta',
        'survey_year' => 2026,
        'status' => 'published',
        'latitude' => -6.7149029,
        'longitude' => 108.5689549,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Admin view
    $responseAdmin = $this->actingAs($this->admin)->get(route('houses.show', $house));
    $responseAdmin->assertOk();
    $responseAdmin->assertSee('id="mini-map"', false);
    $responseAdmin->assertSee('leaflet.js', false);
    $responseAdmin->assertSee('-6.7149029', false);
    $responseAdmin->assertSee('108.5689549', false);
    $responseAdmin->assertSee('Buka Google Maps');

    // Surveyor view
    $responseSurveyor = $this->actingAs($this->surveyor)->get(route('surveyor.rtlh.show', $house));
    $responseSurveyor->assertOk();
    $responseSurveyor->assertSee('id="mini-map"', false);
    $responseSurveyor->assertSee('leaflet.js', false);
    $responseSurveyor->assertSee('-6.7149029', false);
    $responseSurveyor->assertSee('108.5689549', false);
    $responseSurveyor->assertSee('Buka Google Maps');
    $responseSurveyor->assertSee('#eab308', false); // Belum Terealisasi yellow color
});

test('public map view renders realization filters, legend and dynamic marker colors', function () {
    $response = $this->get(route('public.map'));
    $response->assertOk();
    $response->assertSee('id="realization_status"', false);
    $response->assertSee('Semua Realisasi');
    $response->assertSee('Sudah Terealisasi');
    $response->assertSee('Belum Terealisasi');
    $response->assertSee('pin-realized');
    $response->assertSee('pin-not-realized');
});

test('public rtlh api outputs realization status and supports filtering by realization status', function () {
    $realizedHouse = House::create([
        'house_code' => 'RTLH-MAP-REAL-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990001',
        'head_of_household_name' => 'Warga Sudah Realisasi',
        'survey_year' => 2026,
        'is_public' => true,
        'status' => 'published',
        'realization_status' => 'realized',
        'latitude' => -6.7149,
        'longitude' => 108.5689,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $notRealizedHouse = House::create([
        'house_code' => 'RTLH-MAP-UNREAL-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990002',
        'head_of_household_name' => 'Warga Belum Realisasi',
        'survey_year' => 2026,
        'is_public' => true,
        'status' => 'published',
        'realization_status' => 'not_realized',
        'latitude' => -6.7150,
        'longitude' => 108.5690,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Test default API list includes realization_status
    $resAll = $this->getJson('/api/v1/public/rtlh');
    $resAll->assertOk();
    $dataAll = collect($resAll->json('data'));
    $foundRealized = $dataAll->firstWhere('house_code', 'RTLH-MAP-REAL-01');
    expect($foundRealized)->not->toBeNull();
    expect($foundRealized['realization_status'])->toBe('realized');

    // Test filter realized only
    $resRealized = $this->getJson('/api/v1/public/rtlh?realization_status=realized');
    $resRealized->assertOk();
    $dataRealized = collect($resRealized->json('data'));
    expect($dataRealized->pluck('house_code'))->toContain('RTLH-MAP-REAL-01');
    expect($dataRealized->pluck('house_code'))->not->toContain('RTLH-MAP-UNREAL-01');

    // Test filter not_realized only
    $resNotRealized = $this->getJson('/api/v1/public/rtlh?realization_status=not_realized');
    $resNotRealized->assertOk();
    $dataNotRealized = collect($resNotRealized->json('data'));
    expect($dataNotRealized->pluck('house_code'))->toContain('RTLH-MAP-UNREAL-01');
    expect($dataNotRealized->pluck('house_code'))->not->toContain('RTLH-MAP-REAL-01');

    // Test submitted house with coordinates also appears in map API
    $submittedHouse = House::create([
        'house_code' => 'RTLH-MAP-SUBMITTED-01',
        'region_id' => $this->region->id,
        'nik' => '3274010101990003',
        'head_of_household_name' => 'Warga Submitted',
        'survey_year' => 2026,
        'is_public' => false,
        'status' => 'submitted',
        'realization_status' => 'not_realized',
        'latitude' => -6.7155,
        'longitude' => 108.5695,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Petugas (surveyor/admin) dapat melihat submitted house di peta
    $resSubmitted = $this->actingAs($this->surveyor)->getJson('/api/v1/public/rtlh');
    $resSubmitted->assertOk();
    $codes = collect($resSubmitted->json('data'))->pluck('house_code');
    expect($codes)->toContain('RTLH-MAP-SUBMITTED-01');

    // Pengunjung publik tanpa login tidak boleh melihat submitted house
    $this->app['auth']->forgetGuards();
    $resGuest = $this->getJson('/api/v1/public/rtlh');
    $resGuest->assertOk();
    $guestCodes = collect($resGuest->json('data'))->pluck('house_code');
    expect($guestCodes)->not->toContain('RTLH-MAP-SUBMITTED-01');
});

