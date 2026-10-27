<?php

use App\Models\House;
use App\Models\HouseUtility;
use App\Models\MasterCategory;
use App\Models\MasterValue;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-land-utility@cirebon-rtlh.test'],
        [
            'name' => 'Admin Land Utility Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-land-utility@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Land Utility Test',
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

test('land status master values include milik sendiri, saudara, anak, and orang tua', function () {
    $cat = MasterCategory::where('code', 'LAND_STATUS')->firstOrFail();
    $labels = MasterValue::where('master_category_id', $cat->id)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->pluck('label')
        ->all();

    expect($labels)->toContain('MILIK SENDIRI', 'SAUDARA', 'ANAK', 'ORANG TUA');
});

test('house create form renders land status options and ventilation select', function () {
    $response = $this->actingAs($this->surveyor)->get(route('houses.create'));

    $response->assertStatus(200);
    $response->assertSee('MILIK SENDIRI');
    $response->assertSee('SAUDARA');
    $response->assertSee('ANAK');
    $response->assertSee('ORANG TUA');
    $response->assertSee('Ventilasi Udara');
    $response->assertSee('Bukaan Cahaya / Jendela');
    $response->assertSee('name="ventilation"', false);
    $response->assertSee('name="light_opening"', false);
});

test('surveyor can save house with land status, ventilation, and light opening', function () {
    $cat = MasterCategory::where('code', 'LAND_STATUS')->firstOrFail();
    $saudaraVal = MasterValue::where('master_category_id', $cat->id)->where('code', 'SIBLING')->firstOrFail();

    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), [
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'land_status_id' => $saudaraVal->id,
        'ventilation' => 'Ada',
        'light_opening' => 'Ada',
        'submit' => '0',
    ]);

    $response->assertSessionHas('ok');

    $house = House::where('region_id', $this->region->id)
        ->where('created_by', $this->surveyor->id)
        ->latest('id')
        ->firstOrFail();

    expect($house->land_status_id)->toBe($saudaraVal->id);

    $utility = HouseUtility::where('house_id', $house->id)->firstOrFail();
    expect($utility->ventilation)->toBe('Ada');
    expect($utility->light_opening)->toBe('Ada');

    // Show view renders values
    $showRes = $this->actingAs($this->surveyor)->get(route('houses.show', $house));
    $showRes->assertStatus(200);
    $showRes->assertSee('SAUDARA');
    $showRes->assertSee('Ventilasi Udara:');
    $showRes->assertSee('Bukaan Cahaya / Jendela:');
});

test('surveyor can edit sanitation and utility with ventilation and light opening select', function () {
    $house = House::create([
        'region_id' => $this->region->id,
        'house_code' => 'TEST-UTIL-01',
        'survey_year' => 2026,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
        'status' => 'draft',
    ]);

    $editRes = $this->actingAs($this->surveyor)->get(route('houses.sanitation-utility.edit', $house));
    $editRes->assertStatus(200);
    $editRes->assertSee('name="ventilation"', false);
    $editRes->assertSee('name="light_opening"', false);

    $updateRes = $this->actingAs($this->surveyor)->put(route('houses.sanitation-utility.update', $house), [
        'toilet_available' => '1',
        'ventilation' => 'Tidak Ada',
        'light_opening' => 'Ada',
    ]);

    $updateRes->assertRedirect(route('houses.show', $house));

    $utility = HouseUtility::where('house_id', $house->id)->firstOrFail();
    expect($utility->ventilation)->toBe('Tidak Ada');
    expect($utility->light_opening)->toBe('Ada');
});
