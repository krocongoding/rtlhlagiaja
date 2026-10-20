<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Support\DataMasker;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-sec-test@cirebon-rtlh.test'],
        ['name' => 'Admin Sec', 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyorA = User::firstOrCreate(
        ['email' => 'surveyor-a-sec@cirebon-rtlh.test'],
        ['name' => 'Surveyor A', 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (!$this->surveyorA->hasRole('surveyor')) {
        $this->surveyorA->roles()->attach($surveyorRole);
    }

    $this->surveyorB = User::firstOrCreate(
        ['email' => 'surveyor-b-sec@cirebon-rtlh.test'],
        ['name' => 'Surveyor B', 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (!$this->surveyorB->hasRole('surveyor')) {
        $this->surveyorB->roles()->attach($surveyorRole);
    }

    $this->region = Region::firstOrCreate(
        ['code' => '32.74.01.1002'],
        ['name' => 'Sukapura', 'type' => 'village', 'level' => 2, 'is_active' => true]
    );
});

test('data masker masks 16-digit NIK and phone correctly', function () {
    expect(DataMasker::maskNik('3209123456780001'))->toBe('320912******0001');
    expect(DataMasker::maskNik(''))->toBe('-');
    expect(DataMasker::maskNik(null))->toBe('-');
    expect(DataMasker::maskPhone('081234567890'))->toBe('0812****7890');
});

test('public api only returns published and is_public houses', function () {
    // House 1: draft (should NOT appear)
    House::create([
        'house_code' => 'RTLH-SEC-DRAFT',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'draft',
        'is_public' => false,
        'created_by' => $this->surveyorA->id,
    ]);

    // House 2: submitted review (should NOT appear)
    House::create([
        'house_code' => 'RTLH-SEC-SUBMITTED',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'submitted',
        'is_public' => false,
        'created_by' => $this->surveyorA->id,
    ]);

    // House 3: verified but not published yet (should NOT appear)
    House::create([
        'house_code' => 'RTLH-SEC-VERIFIED',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'verified',
        'is_public' => false,
        'created_by' => $this->surveyorA->id,
    ]);

    // House 4: published (MUST appear)
    $publishedHouse = House::create([
        'house_code' => 'RTLH-SEC-PUBLISHED',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'latitude' => -6.715000,
        'longitude' => 108.555000,
        'created_by' => $this->surveyorA->id,
    ]);

    $response = $this->getJson(route('api.public.rtlh'));
    $response->assertOk();

    $houseCodes = collect($response->json('data'))->pluck('house_code')->all();

    expect($houseCodes)->toContain('RTLH-SEC-PUBLISHED');
    expect($houseCodes)->not->toContain('RTLH-SEC-DRAFT');
    expect($houseCodes)->not->toContain('RTLH-SEC-SUBMITTED');
    expect($houseCodes)->not->toContain('RTLH-SEC-VERIFIED');
});

test('public coordinate is fuzzed for guests but exact for authenticated users', function () {
    $house = House::create([
        'house_code' => 'RTLH-SEC-COORD',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'latitude' => -6.720000,
        'longitude' => 108.550000,
        'created_by' => $this->surveyorA->id,
    ]);

    // 1. Akses publik (Guest)
    $resGuest = $this->getJson(route('api.public.rtlh', ['search' => 'RTLH-SEC-COORD']));
    $resGuest->assertOk();
    $guestItem = collect($resGuest->json('data'))->firstWhere('house_code', 'RTLH-SEC-COORD');
    expect($guestItem)->not->toBeNull();

    [$guestLng, $guestLat] = $guestItem['coordinates'];
    // Fuzzed: berbeda dari koordinat asli tapi dekat
    expect($guestLat)->not->toEqual(-6.720000);
    expect(abs($guestLat - (-6.720000)))->toBeLessThan(0.002);

    // 2. Akses petugas (Admin login)
    $resAuth = $this->actingAs($this->admin)->getJson(route('api.public.rtlh', ['search' => 'RTLH-SEC-COORD']));
    $resAuth->assertOk();
    $authItem = collect($resAuth->json('data'))->firstWhere('house_code', 'RTLH-SEC-COORD');

    [$authLng, $authLat] = $authItem['coordinates'];
    // Exact: 100% presisi untuk petugas
    expect($authLat)->toEqual(-6.720000);
    expect($authLng)->toEqual(108.550000);
});

test('idor check: surveyor cannot edit house created by another surveyor', function () {
    $houseB = House::create([
        'house_code' => 'RTLH-SEC-SURVEYOR-B',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'draft',
        'is_public' => false,
        'created_by' => $this->surveyorB->id,
    ]);

    // Surveyor A mencoba edit data milik Surveyor B via route houses.edit
    $resA = $this->actingAs($this->surveyorA)->get(route('houses.edit', $houseB));
    $resA->assertForbidden();

    // Surveyor B (pemilik) boleh edit
    $resB = $this->actingAs($this->surveyorB)->get(route('houses.edit', $houseB));
    $resB->assertOk();

    // Admin boleh edit data mana pun
    $resAdmin = $this->actingAs($this->admin)->get(route('houses.edit', $houseB));
    $resAdmin->assertOk();
});

test('security headers are present in web responses', function () {
    $response = $this->get('/');
    $response->assertOk();

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('login api is rate limited after too many attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->postJson(route('api.auth.login'), [
            'email' => 'unknown@example.com',
            'password' => 'wrong-pass',
        ]);
    }

    $response = $this->postJson(route('api.auth.login'), [
        'email' => 'unknown@example.com',
        'password' => 'wrong-pass',
    ]);

    $response->assertStatus(429);
});
