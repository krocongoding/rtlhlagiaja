<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CpbmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $cpbmId = $this->route('cpbm')?->id ?? $this->route('cpbm');

        return [
            'nik' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('cpbms', 'nik')->ignore($cpbmId)],
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'rt' => 'nullable|string|max:10',
            'rw' => 'nullable|string|max:10',
            'district_id' => 'nullable|exists:regions,id',
            'region_id' => 'required|exists:regions,id',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam data CPBM.',
            'name.required' => 'Nama Kepala Keluarga wajib diisi.',
            'address.required' => 'Alamat (Nama Jalan / Blok) wajib diisi.',
            'region_id.required' => 'Kelurahan wajib dipilih.',
            'region_id.exists' => 'Kelurahan yang dipilih tidak valid.',
        ];
    }
}
