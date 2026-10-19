<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class HouseRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return [
            'cpbm_id' => 'nullable|exists:cpbms,id',
            'region_id' => 'required|exists:regions,id',
            'house_code' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:20',
            'head_of_household_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:2000',
            'block' => 'nullable|string|max:100',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'area_m2' => 'nullable|numeric|min:0|max:10000',
            'occupant_count' => 'nullable|integer|min:0|max:100',
            'household_count' => 'nullable|integer|min:0|max:50',
            'survey_year' => 'required|integer|min:2000|max:2100',
            // Batas wilayah Kota Cirebon: Lat -6.7500 s.d. -6.6900, Lng 108.5350 s.d. 108.5800
            'latitude' => 'nullable|numeric|between:-6.7500,-6.6900',
            'longitude' => 'nullable|numeric|between:108.5350,108.5800',
            'settlement_condition_id' => 'nullable|exists:master_values,id',
            'room_function_id' => 'nullable|exists:master_values,id',
            'ownership_status_id' => 'nullable|exists:master_values,id',
            'land_status_id' => 'nullable|exists:master_values,id',
            'foundation' => 'nullable|string|max:100',
            'sloof_condition_id' => 'nullable|exists:master_values,id',
            'column_condition_id' => 'nullable|exists:master_values,id',
            'beam_condition_id' => 'nullable|exists:master_values,id',
            'floor_material_id' => 'nullable|exists:master_values,id',
            'floor_condition_id' => 'nullable|exists:master_values,id',
            'wall_material_id' => 'nullable|exists:master_values,id',
            'wall_condition_id' => 'nullable|exists:master_values,id',
            'ceiling_condition_id' => 'nullable|exists:master_values,id',
            'roof_frame_material_id' => 'nullable|exists:master_values,id',
            'roof_frame_condition_id' => 'nullable|exists:master_values,id',
            'roof_material_id' => 'nullable|exists:master_values,id',
            'roof_condition_id' => 'nullable|exists:master_values,id',
            'water_source_id' => 'nullable|exists:master_values,id',
            'wastewater_disposal_id' => 'nullable|exists:master_values,id',
            'toilet_available' => 'nullable|boolean',
            'toilet_type_id' => 'nullable|exists:master_values,id',
            'fecal_disposal_type_id' => 'nullable|exists:master_values,id',
            'water_fecal_distance' => 'nullable|string|max:50',
            'light_opening' => 'nullable|string|max:30',
            'ventilation' => 'nullable|string|max:30',
            'lighting_source_id' => 'nullable|exists:master_values,id',
            'funding_source' => 'nullable|string|max:100',
            'budget_amount' => 'nullable|numeric|min:0',
            'proposal_document' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:10240',
            'kk_document' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:10240',
            'akte_document' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:10240',
            'land_document' => 'nullable|file|mimes:pdf,doc,docx,zip,rar,jpg,jpeg,png|max:10240',
            'proposal_notes' => 'nullable|string|max:2000',
            'realization_status' => 'nullable|string|in:not_realized,realized',
            'assessment_notes' => 'nullable|string|max:5000',
            'photo_front' => 'nullable|image|max:5120',
            'photo_angle' => 'nullable|image|max:5120',
            'photo_side' => 'nullable|image|max:5120',
            'photo_back' => 'nullable|image|max:5120',
            'photo_family_room' => 'nullable|image|max:5120',
            'photo_bathroom' => 'nullable|image|max:5120',
        ];
    }
}
