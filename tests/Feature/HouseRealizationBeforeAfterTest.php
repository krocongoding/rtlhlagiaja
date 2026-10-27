<?php

use App\Models\House;
use App\Models\HousePhoto;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-before-after@cirebon-rtlh.test'],
        ['name' => 'Admin BA', 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (! $this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-before-after@cirebon-rtlh.test'],
        ['name' => 'Surveyor BA', 'password' => bcrypt('password'), 'is_active' => true]
    );
    if (! $this->surveyor->hasRole('surveyor')) {
        $this->surveyor->roles()->attach($surveyorRole);
    }

    $this->region = Region::firstOrCreate(
        ['code' => '32.74.01.1001'],
        ['name' => 'Kesenden', 'type' => 'village', 'level' => 2, 'is_active' => true]
    );
});

test('house photos support before and after phases with dedicated scopes', function () {
    $house = House::create([
        'house_code' => 'RTLH-BA-01',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'realized',
        'created_by' => $this->surveyor->id,
    ]);

    $beforePhoto = HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'before',
        'path' => 'rtlh/test/before.jpg',
        'disk' => 'public',
    ]);

    $afterPhoto = HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'after',
        'path' => 'rtlh/test/after.jpg',
        'disk' => 'public',
    ]);

    expect($house->photosBefore()->count())->toBe(1);
    expect($house->photosAfter()->count())->toBe(1);
    expect($house->photosBefore->first()->id)->toBe($beforePhoto->id);
    expect($house->photosAfter->first()->id)->toBe($afterPhoto->id);
});

test('surveyor and admin can upload after photos and update realized score', function () {
    Storage::fake('public');

    $house = House::create([
        'house_code' => 'RTLH-BA-02',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'realization_status' => 'not_realized',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Initial before photo
    HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'before',
        'path' => 'rtlh/test/before.jpg',
        'disk' => 'public',
    ]);

    $fakeAfterFront = UploadedFile::fake()->image('after_front.jpg', 800, 600);
    $fakeAfterRoom = UploadedFile::fake()->image('after_room.jpg', 800, 600);

    $response = $this->actingAs($this->surveyor)
        ->post(route('houses.realization.update', $house), [
            'realization_status' => 'realized',
            'funding_source' => 'APBD Kota Cirebon',
            'budget_amount' => 20000000,
            'realized_score' => 0,
            'photo_after_front' => $fakeAfterFront,
            'photo_after_family_room' => $fakeAfterRoom,
        ]);

    $response->assertSessionHas('ok');

    $house->refresh();

    expect($house->realization_status)->toBe('realized');
    expect($house->realized_score)->toBe(0);
    expect($house->photosAfter()->count())->toBe(2);

    $frontAfter = $house->photosAfter()->where('type', 'front')->first();
    expect($frontAfter)->not->toBeNull();
    expect($frontAfter->phase)->toBe('after');
    Storage::disk('public')->assertExists($frontAfter->path);
});

test('public api detail includes transformation data and before after photos for realized houses', function () {
    $house = House::create([
        'house_code' => 'RTLH-BA-03',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'realization_status' => 'realized',
        'realized_score' => 0,
        'funding_source' => 'BAZNAS Kota Cirebon',
        'budget_amount' => 15000000,
        'created_by' => $this->surveyor->id,
    ]);

    HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'before',
        'path' => 'rtlh/test/before.jpg',
        'disk' => 'public',
    ]);

    HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'after',
        'path' => 'rtlh/test/after.jpg',
        'disk' => 'public',
    ]);

    $response = $this->getJson("/api/v1/public/rtlh/{$house->id}");
    $response->assertOk();

    $data = $response->json('data');
    expect($data['has_transformation'])->toBeTrue();
    expect($data['photos_before'])->toHaveCount(1);
    expect($data['photos_after'])->toHaveCount(1);
    expect($data['realization']['status'])->toBe('realized');
    expect($data['realization']['realized_score'])->toBe(0);
});

test('public detail view renders before after transformation section for realized house', function () {
    $house = House::create([
        'house_code' => 'RTLH-BA-04',
        'region_id' => $this->region->id,
        'survey_year' => 2026,
        'status' => 'published',
        'is_public' => true,
        'realization_status' => 'realized',
        'realized_score' => 0,
        'funding_source' => 'APBD Kota Cirebon',
        'budget_amount' => 20000000,
        'created_by' => $this->surveyor->id,
    ]);

    HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'before',
        'path' => 'rtlh/test/before.jpg',
        'disk' => 'public',
    ]);

    HousePhoto::create([
        'house_id' => $house->id,
        'type' => 'front',
        'phase' => 'after',
        'path' => 'rtlh/test/after.jpg',
        'disk' => 'public',
    ]);

    $response = $this->get(route('public.rtlh.detail', $house));
    $response->assertOk();
    $response->assertSee('Transformasi Rumah Layak Huni (Before & After)', false);
    $response->assertSee('Foto Sebelum Bantuan');
    $response->assertSee('Foto Sesudah Bantuan (Layak Huni)');
    $response->assertSee('SEBELUM (Kondisi Awal RTLH)');
    $response->assertSee('SESUDAH (Rehabilitasi Layak Huni)');
});
