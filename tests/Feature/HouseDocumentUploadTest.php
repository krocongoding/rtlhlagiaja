<?php

use App\Models\House;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $surveyorRole = Role::firstOrCreate(['slug' => 'surveyor'], ['name' => 'Surveyor']);

    $this->admin = User::firstOrCreate(
        ['email' => 'admin-doc-test@cirebon-rtlh.test'],
        [
            'name' => 'Admin Doc Test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]
    );
    if (!$this->admin->hasRole('admin')) {
        $this->admin->roles()->attach($adminRole);
    }

    $this->surveyor = User::firstOrCreate(
        ['email' => 'surveyor-doc-test@cirebon-rtlh.test'],
        [
            'name' => 'Surveyor Doc Test',
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

test('surveyor and admin can upload KK, Akte, Surat Tanah, and Proposal separately', function () {
    Storage::fake('public');

    $kkFile = UploadedFile::fake()->create('kartu_keluarga.pdf', 300, 'application/pdf');
    $akteFile = UploadedFile::fake()->create('akte_kelahiran.jpg', 250, 'image/jpeg');
    $landFile = UploadedFile::fake()->create('sertifikat_tanah.pdf', 400, 'application/pdf');
    $proposalFile = UploadedFile::fake()->create('proposal_usulan.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->surveyor)->post(route('surveyor.rtlh.store'), [
        'region_id' => $this->region->id,
        'nik' => '3274010101990001',
        'head_of_household_name' => 'Warga Dokumen Lengkap',
        'survey_year' => 2026,
        'funding_source' => 'APBD Kabupaten Cirebon',
        'budget_amount' => 20000000,
        'proposal_notes' => 'Proposal terverifikasi desa.',
        'kk_document' => $kkFile,
        'akte_document' => $akteFile,
        'land_document' => $landFile,
        'proposal_document' => $proposalFile,
    ]);

    $response->assertSessionHas('ok');

    $house = House::where('nik', '3274010101990001')->first();
    expect($house)->not->toBeNull();
    expect($house->kk_document_path)->not->toBeNull();
    expect($house->akte_document_path)->not->toBeNull();
    expect($house->land_document_path)->not->toBeNull();
    expect($house->proposal_document_path)->not->toBeNull();

    Storage::disk('public')->assertExists($house->kk_document_path);
    Storage::disk('public')->assertExists($house->akte_document_path);
    Storage::disk('public')->assertExists($house->land_document_path);
    Storage::disk('public')->assertExists($house->proposal_document_path);

    // Verify detail show view renders each document link separately
    $showResponse = $this->actingAs($this->admin)->get(route('houses.show', $house));
    $showResponse->assertOk();
    $showResponse->assertSee('1. Kartu Keluarga (KK)');
    $showResponse->assertSee('2. Akte / Akta');
    $showResponse->assertSee('3. Surat Tanah');
    $showResponse->assertSee('4. Proposal Usulan');
    $showResponse->assertSee('Buka Dokumen KK');
    $showResponse->assertSee('Buka Dokumen Akte');
    $showResponse->assertSee('Buka Surat Tanah');
    $showResponse->assertSee('Buka Proposal');
});

test('house edit form step 3 renders separate upload dropzones for KK, Akte, Surat Tanah, and Proposal', function () {
    $house = House::create([
        'house_code' => 'RTLH-DOC-002',
        'region_id' => $this->region->id,
        'nik' => '3274010101990002',
        'head_of_household_name' => 'Warga Form Dokumen',
        'survey_year' => 2026,
        'status' => 'draft',
        'created_by' => $this->surveyor->id,
        'updated_by' => $this->surveyor->id,
    ]);

    // Admin view
    $resAdmin = $this->actingAs($this->admin)->get(route('houses.edit', $house));
    $resAdmin->assertOk();
    $resAdmin->assertSee('id="kk_document"', false);
    $resAdmin->assertSee('id="akte_document"', false);
    $resAdmin->assertSee('id="land_document"', false);
    $resAdmin->assertSee('id="proposal_document"', false);
    $resAdmin->assertSee('Berkas Kartu Keluarga (KK)');
    $resAdmin->assertSee('Berkas Akte / Akta Kependudukan');
    $resAdmin->assertSee('Berkas Surat Tanah / Sertifikat Kepemilikan Lahan');
    $resAdmin->assertSee('Berkas Proposal Usulan / Surat Pengantar Desa');

    // Surveyor view
    $resSurveyor = $this->actingAs($this->surveyor)->get(route('surveyor.rtlh.edit', $house));
    $resSurveyor->assertOk();
    $resSurveyor->assertSee('id="kk_document"', false);
    $resSurveyor->assertSee('id="akte_document"', false);
    $resSurveyor->assertSee('id="land_document"', false);
    $resSurveyor->assertSee('id="proposal_document"', false);
});
