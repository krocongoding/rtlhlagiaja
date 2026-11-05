<?php

use App\Models\Cpbm;
use App\Models\Region;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->surveyor = createSurveyorUser('surveyor-api-test@cirebon-rtlh.test', 'Surveyor API Test');
    $this->district = Region::create([
        'code' => '32.74.01',
        'name' => 'KEJAKSAN',
        'type' => 'district',
        'level' => 3,
        'is_active' => true,
    ]);
    $this->village = Region::create([
        'code' => '32.74.01.1001',
        'name' => 'KEJAKSAN',
        'type' => 'village',
        'level' => 4,
        'parent_id' => $this->district->id,
        'is_active' => true,
    ]);
});

test('surveyor can search cpbm via API', function () {
    Cpbm::create([
        'nik' => '3274011234560001',
        'name' => 'Sutrisno Warga',
        'address' => 'Jl. Siliwangi No. 10',
        'rt' => '01',
        'rw' => '02',
        'region_id' => $this->village->id,
        'district_id' => $this->district->id,
    ]);

    Sanctum::actingAs($this->surveyor, ['*']);

    $response = $this->getJson(route('api.surveyor.cpbm.search', ['q' => 'Sutrisno']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment([
            'nik' => '3274011234560001',
            'name' => 'Sutrisno Warga',
        ]);
});

test('surveyor can create new cpbm via API', function () {
    Sanctum::actingAs($this->surveyor, ['*']);

    $payload = [
        'nik' => '3274029999990001',
        'name' => 'Ahmad Suhendar',
        'phone' => '081234567890',
        'address' => 'Jl. Kesambi Gang Melati',
        'rt' => '03',
        'rw' => '05',
        'region_id' => $this->village->id,
    ];

    $response = $this->postJson(route('api.surveyor.cpbm.store'), $payload);

    $response->assertCreated()
        ->assertJsonFragment([
            'nik' => '3274029999990001',
            'name' => 'Ahmad Suhendar',
        ]);

    $this->assertDatabaseHas('cpbms', [
        'nik' => '3274029999990001',
        'name' => 'Ahmad Suhendar',
        'district_id' => $this->district->id,
        'created_by' => $this->surveyor->id,
    ]);
});
