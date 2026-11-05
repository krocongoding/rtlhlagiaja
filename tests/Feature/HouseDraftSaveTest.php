<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = createAdminUser('admin-draft-test@cirebon-rtlh.test', 'Admin Draft Test');
    $this->surveyor = createSurveyorUser('surveyor-draft-test@cirebon-rtlh.test', 'Surveyor Draft Test');
    $this->region = getTestVillage();
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

test('house form renders Simpan Draft buttons without DOM named property shadowing on submit buttons', function () {
    $responseSurveyor = $this->actingAs($this->surveyor)->get(route('surveyor.rtlh.create'));
    $responseSurveyor->assertOk();
    $responseSurveyor->assertSee('submitAsDraft()');
    $responseSurveyor->assertSee('Simpan Draft');
    $responseSurveyor->assertSee('HTMLFormElement.prototype.submit.call(form)', false);
    $responseSurveyor->assertDontSee('<button type="submit" class="btn primary" name="submit"', false);

    $responseAdmin = $this->actingAs($this->admin)->get(route('houses.create'));
    $responseAdmin->assertOk();
    $responseAdmin->assertSee('submitAsDraft()');
    $responseAdmin->assertSee('Simpan Draft');
    $responseAdmin->assertSee('HTMLFormElement.prototype.submit.call(form)', false);
    $responseAdmin->assertDontSee('<button type="submit" class="btn primary" name="submit"', false);
});

test('surveyor can save draft without specifying survey_year and it defaults to current year', function () {
    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), [
        'region_id' => $this->region->id,
        'submit' => '0',
    ]);

    $response->assertSessionHas('ok', 'Data berhasil disimpan sebagai draft.');

    $house = House::where('region_id', $this->region->id)
        ->where('created_by', $this->surveyor->id)
        ->latest('id')
        ->first();

    expect($house)->not->toBeNull();
    expect($house->status)->toBe('draft');
    expect($house->survey_year)->toBe((int) date('Y'));
});

test('surveyor can update an existing draft and status remains draft', function () {
    $house = House::create([
        'region_id' => $this->region->id,
        'house_code' => 'RTLH-TEST-DRAFT-01',
        'survey_year' => 2026,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
        'status' => 'draft',
        'is_public' => false,
    ]);

    $response = $this->actingAs($this->surveyor)->put(route('surveyor.rtlh.update', $house), [
        'region_id' => $this->region->id,
        'head_of_household_name' => 'Nama Kepala Draft Baru',
        'submit' => '0',
    ]);

    $response->assertSessionHas('ok', 'Data berhasil diperbarui.');

    $house->refresh();
    expect($house->status)->toBe('draft');
    expect($house->head_of_household_name)->toBe('Nama Kepala Draft Baru');
});
