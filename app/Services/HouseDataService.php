<?php

namespace App\Services;

use App\Models\House;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HouseDataService
{
    /**
     * Generate kode yang mudah ditelusuri per desa/kelurahan, misalnya
     * RTLH-KALIBUNTU-0001. Indeks unik pada kolom house_code tetap menjadi
     * pengaman terakhir agar tidak ada kode yang sama.
     */
    public function generateUniqueCode(int $regionId): string
    {
        $region = Region::findOrFail($regionId);
        $regionSlug = Str::upper(Str::slug($region->name, '-'));
        $prefix = 'RTLH-' . Str::substr($regionSlug ?: $region->code, 0, 40);

        $lastCode = House::query()
            ->where('house_code', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('house_code');

        $sequence = $lastCode && preg_match('/-(\d+)$/', $lastCode, $matches)
            ? ((int) $matches[1]) + 1
            : 1;

        do {
            $code = sprintf('%s-%04d', $prefix, $sequence++);
        } while (House::where('house_code', $code)->exists());

        return $code;
    }

    public function save(House $house, array $data, int $userId, bool $submit = false): House
    {
        return DB::transaction(function () use ($house, $data, $userId, $submit) {
            $lat = (isset($data['latitude']) && $data['latitude'] !== '' && $data['latitude'] !== null) ? (float) $data['latitude'] : null;
            $lng = (isset($data['longitude']) && $data['longitude'] !== '' && $data['longitude'] !== null) ? (float) $data['longitude'] : null;

            $assessmentNotes = $data['assessment_notes'] ?? null;
            $ceilingConditionId = $data['ceiling_condition_id'] ?? null;

            // Separate relational attributes
            $structure = [];
            foreach (['foundation', 'sloof_condition_id', 'column_condition_id', 'beam_condition_id'] as $k) {
                $structure[$k] = $data[$k] ?? null;
            }

            $floor = [];
            foreach (['floor_material_id' => 'material_id', 'floor_condition_id' => 'condition_id'] as $from => $to) {
                $floor[$to] = $data[$from] ?? null;
            }

            $wall = [];
            foreach (['wall_material_id' => 'material_id', 'wall_condition_id' => 'condition_id'] as $from => $to) {
                $wall[$to] = $data[$from] ?? null;
            }

            $roof = [];
            foreach ([
                'roof_frame_material_id' => 'frame_material_id',
                'roof_frame_condition_id' => 'frame_condition_id',
                'roof_material_id' => 'material_id',
                'roof_condition_id' => 'condition_id'
            ] as $from => $to) {
                $roof[$to] = $data[$from] ?? null;
            }

            $san = [];
            foreach (['water_source_id', 'wastewater_disposal_id', 'toilet_available', 'toilet_type_id', 'fecal_disposal_type_id', 'water_fecal_distance'] as $k) {
                $san[$k] = $data[$k] ?? null;
            }

            $util = [];
            foreach (['light_opening', 'ventilation', 'lighting_source_id'] as $k) {
                $util[$k] = $data[$k] ?? null;
            }

            // Handle document uploads
            $docs = [
                'proposal_document' => ['col' => 'proposal_document_path', 'dir' => 'rtlh/proposals'],
                'kk_document' => ['col' => 'kk_document_path', 'dir' => 'rtlh/documents/kk'],
                'akte_document' => ['col' => 'akte_document_path', 'dir' => 'rtlh/documents/akte'],
                'land_document' => ['col' => 'land_document_path', 'dir' => 'rtlh/documents/land'],
            ];
            foreach ($docs as $field => $cfg) {
                if (isset($data[$field]) && $data[$field] instanceof \Illuminate\Http\UploadedFile) {
                    $docPath = $data[$field]->store($cfg['dir'], 'public');
                    $data[$cfg['col']] = $docPath;
                }
            }

            // Unset extracted fields from house data array
            foreach ([
                'assessment_notes', 'foundation', 'sloof_condition_id', 'column_condition_id', 'beam_condition_id',
                'floor_material_id', 'floor_condition_id', 'wall_material_id', 'wall_condition_id',
                'roof_frame_material_id', 'roof_frame_condition_id', 'roof_material_id', 'roof_condition_id',
                'water_source_id', 'wastewater_disposal_id', 'toilet_available', 'toilet_type_id', 'fecal_disposal_type_id', 'water_fecal_distance',
                'light_opening', 'ventilation', 'lighting_source_id', 'ceiling_condition_id',
                'proposal_document', 'kk_document', 'akte_document', 'land_document'
            ] as $k) {
                unset($data[$k]);
            }

            $data['latitude'] = $lat;
            $data['longitude'] = $lng;
            $data['updated_by'] = $userId;
            // Kode rumah ditetapkan server saat data pertama dibuat dan tidak
            // berubah ketika wilayah atau detail lain diperbarui.
            unset($data['house_code']);

            if ($submit) {
                $data['status'] = 'submitted';
                $data['is_public'] = false;
            }

            if (!isset($data['realization_status']) || empty($data['realization_status'])) {
                $data['realization_status'] = $house->realization_status ?: 'not_realized';
            }

            $house->fill($data);
            $house->save();

            $house->structure()->updateOrCreate(['house_id' => $house->id], $structure);
            $house->floor()->updateOrCreate(['house_id' => $house->id], $floor);
            $house->wall()->updateOrCreate(['house_id' => $house->id], $wall);
            $house->ceiling()->updateOrCreate(['house_id' => $house->id], ['condition_id' => $ceilingConditionId]);
            $house->roof()->updateOrCreate(['house_id' => $house->id], $roof);
            $house->sanitation()->updateOrCreate(['house_id' => $house->id], $san);
            $house->utility()->updateOrCreate(['house_id' => $house->id], $util);

            // Update PostGIS location geometry if lat and lng are provided
            if ($lat !== null && $lng !== null) {
                try {
                    DB::statement('UPDATE houses SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?', [$lng, $lat, $house->id]);
                } catch (\Throwable $e) {
                    // Fallback
                }
            }

            // Sinkronkan penilaian skor otomatis dari data survey fisik
            $assessmentService = app(HouseAssessmentService::class);
            $assessment = $assessmentService->syncAssessment($house, $userId, $assessmentNotes);

            if ($submit) {
                $assessment->update([
                    'status' => 'submitted',
                ]);
            }

            return $house->fresh();
        });
    }

    public function storePhotos(House $house, array $files, int $userId): void
    {
        $map = [
            'photo_front' => 'front',
            'photo_angle' => 'angle_45',
            'photo_side' => 'side',
            'photo_back' => 'back',
            'photo_family_room' => 'family_room',
            'photo_bathroom' => 'bathroom'
        ];

        foreach ($map as $field => $type) {
            if (empty($files[$field])) continue;

            // Remove existing photo of the same type if updating
            $existing = $house->photos()->where('type', $type)->first();
            if ($existing) {
                if ($existing->path && Storage::disk('public')->exists($existing->path)) {
                    Storage::disk('public')->delete($existing->path);
                }
                $existing->delete();
            }

            $path = $files[$field]->store('rtlh/' . $house->house_code, 'public');
            $house->photos()->create([
                'type' => $type,
                'path' => $path,
                'disk' => 'public',
                'uploaded_by' => $userId
            ]);
        }
    }
}
