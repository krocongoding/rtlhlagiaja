<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->admin = createAdminUser('admin-rev-test@cirebon-rtlh.test', 'Admin Rev Test');
    $this->surveyor = createSurveyorUser('surveyor-rev-test@cirebon-rtlh.test', 'Surveyor Rev Test');
    $this->region = getTestVillage();
});

test('admin can return a submitted house to revision with notes', function () {
    $house = House::create([
        'house_code' => 'TEST-REV-001',
        'region_id' => $this->region->id,
        'nik' => '3274010101990001',
        'head_of_household_name' => 'Pak Joko',
        'survey_year' => 2026,
        'status' => 'submitted',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $notes = 'Foto fasad depan buram dan titik GPS bergeser ke luar batas kelurahan. Mohon diperbaiki.';

    $response = $this->actingAs($this->admin)
        ->post(route('admin.review.revision', $house), [
            'revision_notes' => $notes,
        ]);

    $response->assertSessionHas('ok');

    $house->refresh();

    expect($house->status)->toBe('revision');
    expect($house->revision_notes)->toBe($notes);
    expect($house->revised_at)->not->toBeNull();
    expect($house->revised_by)->toBe($this->admin->id);
    expect($house->reviser->id)->toBe($this->admin->id);
});

test('revision request requires revision notes', function () {
    $house = House::create([
        'house_code' => 'TEST-REV-002',
        'region_id' => $this->region->id,
        'nik' => '3274010101990002',
        'head_of_household_name' => 'Bu Joko',
        'survey_year' => 2026,
        'status' => 'submitted',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->from(route('admin.review.index'))
        ->post(route('admin.review.revision', $house), [
            'revision_notes' => '',
        ]);

    $response->assertSessionHasErrors(['revision_notes']);

    $house->refresh();
    expect($house->status)->toBe('submitted');
});

test('surveyor can see admin revision notes on house show page', function () {
    $notes = 'Tolong periksa kembali jenis jamban keluarga dan unggah foto penutup atap.';

    $house = House::create([
        'house_code' => 'TEST-REV-003',
        'region_id' => $this->region->id,
        'nik' => '3274010101990003',
        'head_of_household_name' => 'Warga Test',
        'survey_year' => 2026,
        'status' => 'revision',
        'revision_notes' => $notes,
        'revised_at' => now(),
        'revised_by' => $this->admin->id,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->surveyor)
        ->get(route('surveyor.rtlh.show', $house));

    $response->assertOk();
    $response->assertSee('Catatan Revisi dari Admin / Verifikator');
    $response->assertSee($notes);
});

test('surveyor can see revision notes on edit form', function () {
    $notes = 'Perbaiki koordinat GPS yang melenceng.';

    $house = House::create([
        'house_code' => 'TEST-REV-004',
        'region_id' => $this->region->id,
        'nik' => '3274010101990004',
        'head_of_household_name' => 'Warga Edit Test',
        'survey_year' => 2026,
        'status' => 'revision',
        'revision_notes' => $notes,
        'revised_at' => now(),
        'revised_by' => $this->admin->id,
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->surveyor)
        ->get(route('surveyor.rtlh.edit', $house));

    $response->assertOk();
    $response->assertSee('Instruksi Catatan Revisi dari Verifikator / Admin:');
    $response->assertSee($notes);
});

test('non-admin cannot trigger revision action', function () {
    $house = House::create([
        'house_code' => 'TEST-REV-005',
        'region_id' => $this->region->id,
        'nik' => '3274010101990005',
        'head_of_household_name' => 'Warga Non-Admin',
        'survey_year' => 2026,
        'status' => 'submitted',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->surveyor)
        ->post(route('admin.review.revision', $house), [
            'revision_notes' => 'Catatan liar dari non admin',
        ]);

    $response->assertForbidden();

    $house->refresh();
    expect($house->status)->toBe('submitted');
});

test('admin review index renders revision modal triggers and notes badge', function () {
    $house = House::create([
        'house_code' => 'TEST-REV-006',
        'region_id' => $this->region->id,
        'nik' => '3274010101990006',
        'head_of_household_name' => 'Warga Review Page',
        'survey_year' => 2026,
        'status' => 'submitted',
        'revision_notes' => 'Pernah ada revisi sebelumnya',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.review.index'));

    $response->assertOk();
    $response->assertSee('openRevisionModal');
    $response->assertSee('Ada Catatan Revisi');
    $response->assertSee('Kembalikan untuk Revisi');
});
