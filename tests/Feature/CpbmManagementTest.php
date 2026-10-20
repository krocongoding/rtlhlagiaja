<?php

use App\Models\Cpbm;
use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-cpbm-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin CPBM Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-cpbm-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor CPBM Test',
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

test('admin and surveyor can view cpbm index page', function () {
    $cpbm = Cpbm::create([
        'nik' => '3274011234560001',
        'name' => 'Bapak Supardi',
        'address' => 'Jl. Diponegoro No. 10',
        'region_id' => $this->region->id,
        'created_by' => $this->admin->id,
    ]);

    $responseAdmin = $this->actingAs($this->admin)->get(route('cpbm.index'));
    $responseAdmin->assertOk()
        ->assertSee('Basis Data Calon Penerima Bantuan (CPBM)')
        ->assertSee('Bapak Supardi')
        ->assertSee('327401******0001');

    $responseSurveyor = $this->actingAs($this->surveyor)->get(route('cpbm.index'));
    $responseSurveyor->assertOk()
        ->assertSee('Bapak Supardi');

    // Tamu tanpa login ditolak ke halaman login
    $this->app['auth']->forgetGuards();
    $this->get(route('cpbm.index'))->assertRedirect(route('login'));
});

test('user can create a new cpbm with unique NIK', function () {
    $payload = [
        'nik' => '3274019988770001',
        'no_kk' => '3274019988770002',
        'name' => 'Ibu Siti Aminah',
        'gender' => 'female',
        'birth_date' => '1985-05-15',
        'phone' => '081234567890',
        'address' => 'Gang Melati No. 5',
        'rt' => '003',
        'rw' => '002',
        'region_id' => $this->region->id,
        'occupation' => 'Pedagang',
        'monthly_income' => 1500000,
        'family_member_count' => 4,
        'poverty_status' => 'deserving',
    ];

    $response = $this->actingAs($this->admin)->post(route('cpbm.store'), $payload);
    $response->assertRedirect(route('cpbm.index'))
        ->assertSessionHas('ok', 'Data CPBM berhasil ditambahkan.');

    $this->assertDatabaseHas('cpbms', [
        'nik' => '3274019988770001',
        'name' => 'Ibu Siti Aminah',
        'region_id' => $this->region->id,
    ]);

    // Test duplicate NIK validation
    $duplicateResponse = $this->actingAs($this->admin)->post(route('cpbm.store'), $payload);
    $duplicateResponse->assertSessionHasErrors(['nik']);
});

test('cpbm live search json endpoint returns matching candidates', function () {
    $c1 = Cpbm::create([
        'nik' => '3274020000000001',
        'name' => 'Warga Mulyadi Cirebon',
        'address' => 'Jl. Kesambi',
        'region_id' => $this->region->id,
        'created_by' => $this->admin->id,
    ]);

    $c2 = Cpbm::create([
        'nik' => '3274020000000002',
        'name' => 'Warga Bambang Sutrisno',
        'address' => 'Jl. Cipto',
        'region_id' => $this->region->id,
        'created_by' => $this->admin->id,
    ]);

    // Search by name
    $searchResponse = $this->actingAs($this->surveyor)->getJson(route('cpbm.search', ['q' => 'Mulyadi']));
    $searchResponse->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Warga Mulyadi Cirebon']);

    // Search by NIK
    $searchNik = $this->actingAs($this->surveyor)->getJson(route('cpbm.search', ['q' => '3274020000000002']));
    $searchNik->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Warga Bambang Sutrisno']);
});

test('quick modal store returns json data when requested via ajax', function () {
    $payload = [
        'nik' => '3274031122334455',
        'name' => 'Pak Joko Anwar',
        'no_kk' => '3274031122334400',
        'address' => 'Jl. Karanggetas No. 88',
        'rt' => '001',
        'rw' => '004',
        'region_id' => $this->region->id,
    ];

    $response = $this->actingAs($this->surveyor)
        ->postJson(route('cpbm.store'), $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'nik' => '3274031122334455',
            'name' => 'Pak Joko Anwar',
        ]);

    $this->assertDatabaseHas('cpbms', [
        'nik' => '3274031122334455',
        'name' => 'Pak Joko Anwar',
    ]);
});

test('linking cpbm_id when creating house syncs candidate data and associates relation', function () {
    $cpbm = Cpbm::create([
        'nik' => '3274045566778899',
        'name' => 'Haji Mansyur',
        'address' => 'Jl. Veteran No. 45',
        'rt' => '002',
        'rw' => '005',
        'region_id' => $this->region->id,
        'created_by' => $this->admin->id,
    ]);

    $housePayload = [
        'cpbm_id' => $cpbm->id,
        'nik' => $cpbm->nik,
        'head_of_household_name' => $cpbm->name,
        'address' => $cpbm->address,
        'rt' => $cpbm->rt,
        'rw' => $cpbm->rw,
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'funding_source' => 'APBD Kota Cirebon',
        'realization_status' => 'not_realized',
        'submit' => '0', // simpan draft
    ];

    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), $housePayload);
    $response->assertSessionHas('ok', 'Data berhasil disimpan sebagai draft.');

    $house = House::where('cpbm_id', $cpbm->id)->first();
    expect($house)->not->toBeNull();
    expect($house->head_of_household_name)->toBe('Haji Mansyur');
    expect($house->cpbm)->not->toBeNull();
    expect($house->cpbm->id)->toBe($cpbm->id);
});
