<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class HouseDocumentController extends Controller
{
    /**
     * Download / pratinjau dokumen sensitif warga (KK, Akta, Sertifikat Tanah, Proposal).
     *
     * Hanya pengguna terautentikasi (Admin atau Surveyor pemilik data) yang diizinkan mengakses.
     */
    public function show(Request $request, House $house, string $type): Response
    {
        $user = $request->user();

        // Otorisasi: Admin memiliki akses penuh; Surveyor hanya boleh membuka dokumen dari data yang disurveinya
        if (! $user->hasRole('admin')) {
            abort_unless(
                $user->hasRole('surveyor') && $house->created_by === $user->id,
                403,
                'Anda tidak memiliki wewenang untuk mengakses dokumen ini.'
            );
        }

        $documentMap = [
            'kk'       => $house->kk_document_path,
            'akte'     => $house->akte_document_path,
            'land'     => $house->land_document_path,
            'proposal' => $house->proposal_document_path,
        ];

        abort_unless(array_key_exists($type, $documentMap), 404, 'Jenis dokumen tidak valid.');

        $path = $documentMap[$type];
        abort_if(empty($path), 404, 'Dokumen belum diunggah.');

        // Prioritas disk local (storage/app/private), fallback disk public (jika ada file transisi)
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404, 'File dokumen tidak ditemukan di server.');
    }
}
