<?php

namespace App\Support;

class DataMasker
{
    /**
     * Mask NIK (16 digit).
     * Contoh: 3209123456780001 -> 320912******0001
     * Menyembunyikan tanggal lahir dan kode unik tengah, mempertahankan 6 digit wilayah & 4 digit urutan.
     */
    public static function maskNik(?string $nik): string
    {
        if (empty($nik)) {
            return '-';
        }

        $clean = trim($nik);
        $len = strlen($clean);

        if ($len <= 6) {
            return str_repeat('*', $len);
        }

        if ($len <= 10) {
            return substr($clean, 0, 3) . str_repeat('*', $len - 5) . substr($clean, -2);
        }

        // 16 digit standar
        $prefix = substr($clean, 0, 6);
        $suffix = substr($clean, -4);
        $maskedMiddle = str_repeat('*', $len - 10);

        return "{$prefix}{$maskedMiddle}{$suffix}";
    }

    /**
     * Mask nomor telepon.
     * Contoh: 081234567890 -> 0812****7890
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '-';
        }

        $clean = trim($phone);
        $len = strlen($clean);

        if ($len <= 6) {
            return str_repeat('*', $len);
        }

        $prefix = substr($clean, 0, 4);
        $suffix = substr($clean, -4);
        $maskedMiddle = str_repeat('*', max(3, $len - 8));

        return "{$prefix}{$maskedMiddle}{$suffix}";
    }

    /**
     * Fuzzing / jittering koordinat GPS (~50-80 meter) untuk tampilan publik.
     * Bersifat deterministik berbasis ID rumah agar pin peta tidak berubah posisi setiap refresh.
     * Petugas (Admin/Surveyor terautentikasi) akan tetap mendapatkan koordinat 100% akurat.
     *
     * @return array [float $lat, float $lng]
     */
    public static function fuzzCoordinate(float $lat, float $lng, int $seed): array
    {
        $hash = crc32("cirebon_rtlh_coord_fuzz_{$seed}");

        // Sudut acak 0 - 360 derajat
        $angle = ($hash % 360) * (M_PI / 180.0);

        // Jarak acak antara 45 s.d. 85 meter
        $distanceMeters = 45 + (($hash >> 8) % 40);

        // 1 derajat latitude ~ 111.000 meter
        $deltaLat = ($distanceMeters / 111000.0) * cos($angle);

        // 1 derajat longitude ~ 111.000 * cos(lat)
        $latRad = deg2rad($lat);
        $cosLat = cos($latRad);
        if (abs($cosLat) < 0.0001) {
            $cosLat = 1.0;
        }
        $deltaLng = ($distanceMeters / (111000.0 * $cosLat)) * sin($angle);

        return [
            round($lat + $deltaLat, 6),
            round($lng + $deltaLng, 6),
        ];
    }
}
