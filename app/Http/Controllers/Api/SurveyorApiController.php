<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\HouseOccupant;
use App\Services\AuditLogger;
use App\Services\HouseDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SurveyorApiController extends Controller
{
    public function __construct(private HouseDataService $service) {}

    /*
    |--------------------------------------------------------------------------
    | LIST — daftar rumah milik surveyor
    | GET /api/v1/surveyor/houses
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $query = House::with('region')
            ->where('created_by', $request->user()->id)
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('house_code', 'ilike', "%{$search}%")
                  ->orWhere('address', 'ilike', "%{$search}%");
            });
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        $houses = $query->paginate($perPage);

        $houses->getCollection()->transform(
            fn (House $h) => $this->summaryHouse($h)
        );

        return response()->json($houses);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW — detail satu rumah
    | GET /api/v1/surveyor/houses/{house}
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, House $house): JsonResponse
    {
        $this->authorizeOwner($request, $house);

        $house->load([
            'region',
            'structure.sloofCondition',
            'structure.columnCondition',
            'structure.beamCondition',
            'floor.material',
            'floor.condition',
            'wall.material',
            'wall.condition',
            'ceiling.condition',
            'roof.frameCondition',
            'roof.material',
            'roof.condition',
            'sanitation.waterSource',
            'sanitation.toiletType',
            'sanitation.fecalDisposalType',
            'utility.lightingSource',
            'occupants',
            'photos',
            'latestAssessment.items',
        ]);

        return response()->json([
            'data' => $this->detailHouse($house),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE — buat rumah baru
    | POST /api/v1/surveyor/houses
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateHousePayload($request);

        $submit = (bool) ($validated['submit'] ?? false);
        unset($validated['submit']);

        $house = House::create([
            ...$this->extractMainFields($validated),
            'house_code' => $this->service->generateUniqueCode($validated['region_id']),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'status'     => 'draft',
            'is_public'  => false,
        ]);

        $this->service->save($house, $validated, $request->user()->id, $submit);

        $this->syncOccupants($house, $validated['occupants'] ?? []);

        AuditLogger::log(
            $request->user(),
            $submit ? 'SUBMIT' : 'CREATE',
            'House',
            $house->id,
            null,
            $house->fresh()->toArray()
        );

        return response()->json([
            'message'    => $submit
                ? 'Data berhasil dikirim ke Admin untuk review.'
                : 'Data berhasil disimpan sebagai draft.',
            'data'       => ['id' => $house->id, 'house_code' => $house->house_code],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE — perbarui data rumah
    | PUT /api/v1/surveyor/houses/{house}
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, House $house): JsonResponse
    {
        $this->authorizeOwner($request, $house);

        // Hanya draft atau revision yang boleh diedit
        abort_unless(
            in_array($house->status, ['draft', 'revision']),
            422,
            'Data dengan status ' . $house->status . ' tidak dapat diubah.'
        );

        $validated = $this->validateHousePayload($request, $house->id);

        $submit = (bool) ($validated['submit'] ?? false);
        unset($validated['submit']);

        $old = $house->toArray();

        $this->service->save($house, $validated, $request->user()->id, $submit);

        $this->syncOccupants($house, $validated['occupants'] ?? []);

        AuditLogger::log(
            $request->user(),
            $submit ? 'SUBMIT' : 'UPDATE',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return response()->json([
            'message' => $submit
                ? 'Data berhasil dikirim ke Admin untuk review.'
                : 'Data berhasil diperbarui.',
            'data'    => ['id' => $house->id, 'house_code' => $house->house_code, 'status' => $house->fresh()->status],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SUBMIT — kirim draft ke admin
    | POST /api/v1/surveyor/houses/{house}/submit
    |--------------------------------------------------------------------------
    */

    public function submit(Request $request, House $house): JsonResponse
    {
        $this->authorizeOwner($request, $house);

        abort_unless(
            in_array($house->status, ['draft', 'revision']),
            422,
            'Hanya data berstatus draft atau revision yang dapat disubmit.'
        );

        $old = $house->toArray();

        $house->update(['status' => 'submitted']);

        // Sinkronkan penilaian skor otomatis dari data survei fisik
        $assessmentService = app(\App\Services\HouseAssessmentService::class);
        $assessment = $assessmentService->syncAssessment($house, $request->user()->id);
        $assessment->update(['status' => 'submitted']);

        AuditLogger::log(
            $request->user(),
            'SUBMIT',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return response()->json([
            'message' => 'Data berhasil dikirim ke Admin untuk review.',
            'data'    => ['id' => $house->id, 'status' => 'submitted'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: serialize ringkasan rumah untuk list
    |--------------------------------------------------------------------------
    */

    private function summaryHouse(House $house): array
    {
        return [
            'id'          => $house->id,
            'house_code'  => $house->house_code,
            'cpbm_id'     => $house->cpbm_id,
            'nik'         => $house->nik,
            'head_of_household_name' => $house->head_of_household_name,
            'address'     => $house->address,
            'region'      => $house->region
                ? $house->region->only(['id', 'name', 'code'])
                : null,
            'status'      => $house->status,
            'survey_year' => $house->survey_year,
            'updated_at'  => $house->updated_at?->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: serialize detail lengkap rumah
    |--------------------------------------------------------------------------
    */

    private function detailHouse(House $house): array
    {
        $coordinate = DB::selectOne(
            'SELECT ST_X(location) AS lng, ST_Y(location) AS lat
             FROM houses WHERE id = ? AND location IS NOT NULL',
            [$house->id]
        );

        return [
            'id'                      => $house->id,
            'house_code'              => $house->house_code,
            'cpbm_id'                 => $house->cpbm_id,
            'nik'                     => $house->nik,
            'head_of_household_name'  => $house->head_of_household_name,
            'cpbm'                    => $house->cpbm ? [
                'id' => $house->cpbm->id,
                'name' => $house->cpbm->name,
                'nik' => $house->cpbm->nik,
                'phone' => $house->cpbm->phone,
            ] : null,
            'address'                 => $house->address,
            'block'                   => $house->block,
            'rt'                      => $house->rt,
            'rw'                      => $house->rw,
            'area_m2'                 => $house->area_m2,
            'occupant_count'          => $house->occupant_count,
            'household_count'         => $house->household_count,
            'survey_year'             => $house->survey_year,
            'status'                  => $house->status,
            'settlement_condition_id' => $house->settlement_condition_id,
            'room_function_id'        => $house->room_function_id,
            'ownership_status_id'     => $house->ownership_status_id,
            'land_status_id'          => $house->land_status_id,
            'latitude'                => $coordinate ? (float) $coordinate->lat : $house->latitude,
            'longitude'               => $coordinate ? (float) $coordinate->lng : $house->longitude,
            'region'                  => $house->region?->only(['id', 'code', 'name', 'type']),
            'structure'               => $house->structure ? [
                'foundation'         => $house->structure->foundation,
                'sloof_condition_id' => $house->structure->sloof_condition_id,
                'column_condition_id'=> $house->structure->column_condition_id,
                'beam_condition_id'  => $house->structure->beam_condition_id,
            ] : null,
            'floor' => $house->floor ? [
                'material_id'  => $house->floor->material_id,
                'condition_id' => $house->floor->condition_id,
            ] : null,
            'wall' => $house->wall ? [
                'material_id'  => $house->wall->material_id,
                'condition_id' => $house->wall->condition_id,
            ] : null,
            'ceiling' => $house->ceiling ? [
                'condition_id' => $house->ceiling->condition_id,
            ] : null,
            'roof' => $house->roof ? [
                'frame_condition_id' => $house->roof->frame_condition_id,
                'material_id'        => $house->roof->material_id,
                'condition_id'       => $house->roof->condition_id,
            ] : null,
            'sanitation' => $house->sanitation ? [
                'water_source_id'        => $house->sanitation->water_source_id,
                'toilet_available'       => $house->sanitation->toilet_available,
                'toilet_type_id'         => $house->sanitation->toilet_type_id,
                'fecal_disposal_type_id' => $house->sanitation->fecal_disposal_type_id,
                'water_fecal_distance'   => $house->sanitation->water_fecal_distance,
            ] : null,
            'utility' => $house->utility ? [
                'light_opening'     => $house->utility->light_opening,
                'ventilation'       => $house->utility->ventilation,
                'lighting_source_id'=> $house->utility->lighting_source_id,
            ] : null,
            'occupants' => $house->occupants->map(fn ($o) => [
                'id'                 => $o->id,
                'relationship'       => $o->relationship,
                'gender'             => $o->gender,
                'birth_year'         => $o->birth_year,
                'occupation'         => $o->occupation,
                'education'          => $o->education,
                'is_primary_contact' => $o->is_primary_contact,
            ])->values(),
            'photos' => $house->photos->map(fn ($p) => [
                'id'   => $p->id,
                'type' => $p->type,
                'url'  => $p->path ? asset('storage/' . $p->path) : null,
            ])->values(),
            'latest_assessment' => $house->latestAssessment ? [
                'id'              => $house->latestAssessment->id,
                'assessment_year' => $house->latestAssessment->assessment_year,
                'assessment_date' => $house->latestAssessment->assessment_date?->toDateString(),
                'status'          => $house->latestAssessment->status,
                'score'           => $house->latestAssessment->score ?? null,
                'priority_level'  => $house->latestAssessment->priority_level ?? null,
                'notes'           => $house->latestAssessment->notes,
            ] : null,
            'created_at' => $house->created_at?->toIso8601String(),
            'updated_at' => $house->updated_at?->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: validasi payload rumah (store & update)
    |--------------------------------------------------------------------------
    */

    private function validateHousePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            // Data utama
            'cpbm_id'                => ['nullable', 'integer', 'exists:cpbms,id'],
            'nik'                    => ['nullable', 'string', 'max:20'],
            'head_of_household_name' => ['nullable', 'string', 'max:255'],
            'region_id'               => ['required', 'integer', 'exists:regions,id'],
            'address'                 => ['required', 'string', 'max:500'],
            'block'                   => ['nullable', 'string', 'max:50'],
            'rt'                      => ['nullable', 'string', 'max:10'],
            'rw'                      => ['nullable', 'string', 'max:10'],
            'area_m2'                 => ['nullable', 'numeric', 'min:0'],
            'occupant_count'          => ['nullable', 'integer', 'min:0'],
            'household_count'         => ['nullable', 'integer', 'min:0'],
            'survey_year'             => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'settlement_condition_id' => ['nullable', 'integer', 'exists:master_values,id'],
            'room_function_id'        => ['nullable', 'integer', 'exists:master_values,id'],
            'ownership_status_id'     => ['nullable', 'integer', 'exists:master_values,id'],
            'land_status_id'          => ['nullable', 'integer', 'exists:master_values,id'],
            'latitude'                => ['nullable', 'numeric', 'between:-6.7500,-6.6900'],
            'longitude'               => ['nullable', 'numeric', 'between:108.5350,108.5800'],
            'submit'                  => ['nullable', 'boolean'],

            // Struktur
            'foundation'              => ['nullable', 'string', 'max:255'],
            'sloof_condition_id'      => ['nullable', 'integer', 'exists:master_values,id'],
            'column_condition_id'     => ['nullable', 'integer', 'exists:master_values,id'],
            'beam_condition_id'       => ['nullable', 'integer', 'exists:master_values,id'],

            // Lantai
            'floor_material_id'       => ['nullable', 'integer', 'exists:master_values,id'],
            'floor_condition_id'      => ['nullable', 'integer', 'exists:master_values,id'],

            // Dinding
            'wall_material_id'        => ['nullable', 'integer', 'exists:master_values,id'],
            'wall_condition_id'       => ['nullable', 'integer', 'exists:master_values,id'],

            // Plafon
            'ceiling_condition_id'    => ['nullable', 'integer', 'exists:master_values,id'],

            // Atap
            'roof_frame_condition_id' => ['nullable', 'integer', 'exists:master_values,id'],
            'roof_material_id'        => ['nullable', 'integer', 'exists:master_values,id'],
            'roof_condition_id'       => ['nullable', 'integer', 'exists:master_values,id'],

            // Sanitasi
            'water_source_id'         => ['nullable', 'integer', 'exists:master_values,id'],
            'toilet_available'        => ['nullable', 'boolean'],
            'toilet_type_id'          => ['nullable', 'integer', 'exists:master_values,id'],
            'fecal_disposal_type_id'  => ['nullable', 'integer', 'exists:master_values,id'],
            'water_fecal_distance'    => ['nullable', 'numeric', 'min:0'],

            // Utilitas
            'light_opening'           => ['nullable', 'boolean'],
            'ventilation'             => ['nullable', 'boolean'],
            'lighting_source_id'      => ['nullable', 'integer', 'exists:master_values,id'],

            // Penghuni (opsional, array)
            'occupants'               => ['nullable', 'array'],
            'occupants.*.relationship'      => ['nullable', 'string', 'max:100'],
            'occupants.*.gender'            => ['nullable', 'string', 'in:L,P'],
            'occupants.*.birth_year'        => ['nullable', 'integer', 'min:1900', 'max:2099'],
            'occupants.*.occupation'        => ['nullable', 'string', 'max:100'],
            'occupants.*.education'         => ['nullable', 'string', 'max:100'],
            'occupants.*.is_primary_contact'=> ['nullable', 'boolean'],

            // Catatan assessment
            'assessment_notes'        => ['nullable', 'string'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: ambil field utama House dari payload
    |--------------------------------------------------------------------------
    */

    private function extractMainFields(array $data): array
    {
        $keys = [
            'cpbm_id', 'nik', 'head_of_household_name', 'region_id', 'address', 'block', 'rt', 'rw', 'area_m2',
            'occupant_count', 'household_count', 'survey_year',
            'settlement_condition_id', 'room_function_id',
            'ownership_status_id', 'land_status_id',
            'latitude', 'longitude',
        ];

        return array_intersect_key($data, array_flip($keys));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: sync penghuni — hapus lama, insert baru
    |--------------------------------------------------------------------------
    */

    private function syncOccupants(House $house, array $occupants): void
    {
        if (empty($occupants)) {
            return;
        }

        $house->occupants()->delete();

        $rows = array_map(fn ($o) => [
            'house_id'           => $house->id,
            'relationship'       => $o['relationship']       ?? null,
            'gender'             => $o['gender']             ?? null,
            'birth_year'         => $o['birth_year']         ?? null,
            'occupation'         => $o['occupation']         ?? null,
            'education'          => $o['education']          ?? null,
            'is_primary_contact' => $o['is_primary_contact'] ?? false,
            'created_at'         => now(),
            'updated_at'         => now(),
        ], $occupants);

        HouseOccupant::insert($rows);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: pastikan surveyor hanya akses data miliknya
    |--------------------------------------------------------------------------
    */

    private function authorizeOwner(Request $request, House $house): void
    {
        // Admin boleh akses semua
        if ($request->user()->hasRole('admin')) {
            return;
        }

        abort_unless(
            $house->created_by === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke data ini.'
        );
    }
}
