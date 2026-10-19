<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-draft-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin Draft Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-draft-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Draft Test',
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

test('surveyor can save house as draft with minimal fields and status remains draft', function () {
    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), [
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'submit' => '0',
    ]);

    $response->assertSessionHas('ok', 'Data berhasil disimpan sebagai draft.');

    $house = House::where('region_id', $this->region->id)
        ->where('created_by', $this->surveyor->id)
        ->latest('id')
        ->first();

    expect($house)->not->toBeNull();
    expect($house->status)->toBe('draft');
    expect($house->is_public)->toBeFalse();
    expect($house->house_code)->not->toBeEmpty();
});

test('surveyor can submit house for review with submit=1 and status becomes submitted', function () {
    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), [
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'nik' => '3274010101990099',
        'head_of_household_name' => 'Warga Lengkap',
        'submit' => '1',
    ]);

    $response->assertSessionHas('ok', 'Data berhasil dikirim ke Admin untuk review.');

    $house = House::where('nik', '3274010101990099')->first();
    expect($house)->not->toBeNull();
    expect($house->status)->toBe('submitted');
});

test('admin can save house as draft with submit=0', function () {
    $response = $this->actingAs($this->admin)->post(route('houses.store'), [
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'submit' => '0',
    ]);

    $response->assertSessionHas('success', 'Data RTLH berhasil disimpan sebagai draft.');

    $house = House::where('region_id', $this->region->id)
        ->where('created_by', $this->admin->id)
        ->latest('id')
        ->first();

    expect($house)->not->toBeNull();
    expect($house->status)->toBe('draft');
});

test('house form renders Simpan Draft buttons on all steps and header', function () {
    $responseSurveyor = $this->actingAs($this->surveyor)->get(route('surveyor.rtlh.create'));
    $responseSurveyor->assertOk();
    $responseSurveyor->assertSee('submitAsDraft()');
    $responseSurveyor->assertSee('Simpan Draft');

    $responseAdmin = $this->actingAs($this->admin)->get(route('houses.create'));
    $responseAdmin->assertOk();
    $responseAdmin->assertSee('submitAsDraft()');
    $responseAdmin->assertSee('Simpan Draft');
});
