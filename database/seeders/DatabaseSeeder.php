<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::table('regions')->where('code', '32.09')->exists()) {
            DB::table('regions')->insert([
                'code' => '32.09',
                'name' => 'Kabupaten Cirebon',
                'type' => 'regency',
                'level' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->call([
            RegionSeeder::class,
            KotaCirebonRegionSeeder::class,
        ]);

        foreach ([
            ['Admin', 'admin', 'admin@cirebon-rtlh.test'],
            ['Surveyor', 'surveyor', 'surveyor@cirebon-rtlh.test'],
        ] as [$name, $slug, $email]) {
            $role = DB::table('roles')->where('slug', $slug)->first();
            if (!$role) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $roleId = $role->id;
            }

            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                $uid = DB::table('users')->insertGetId([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_active' => true,
                ]);
            } else {
                $uid = $user->id;
                // Pastikan password tetap 'password' dan aktif
                DB::table('users')->where('id', $uid)->update([
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]);
            }

            DB::table('role_user')->updateOrInsert(['user_id' => $uid, 'role_id' => $roleId], []);
        }

        $cats = [
            'HOUSE_CONDITION' => ['Kondisi Rumah', [['GOOD', 'BAIK'], ['RR_SURFACE', 'RUSAK RINGAN (PERMUKAAN)'], ['RS_MATERIAL', 'RUSAK SEDANG (MATERIAL)'], ['RB', 'RUSAK BERAT']]],
            'FOUNDATION_TYPE' => ['Jenis / Kondisi Pondasi', [['BATU_KALI', 'BATU KALI / BATU BELAH'], ['BETON_BERTULANG', 'BETON BERTULANG / TAPAK'], ['BATA_ROLAG', 'BATA MERAH / ROLAG'], ['KAYU_CERUCUK', 'KAYU / CERUCUK'], ['BAMBU', 'BAMBU'], ['TANPA_PONDASI', 'TANPA PONDASI / MENUMPANG TANAH'], ['OTHER', 'LAINNYA']]],
            'FLOOR_MATERIAL' => ['Bahan Lantai', [['CEMENT', 'SEMEN/PLESTERAN'], ['TILE', 'KERAMIK'], ['EARTH', 'TANAH']]],
            'WALL_MATERIAL' => ['Bahan Dinding', [['PLASTER_GRC', 'PLESTERAN/GRC'], ['BRICK', 'BATA'], ['WOOD', 'KAYU/PAPAN']]],
            'ROOF_MATERIAL' => ['Bahan Atap', [['ASBESTOS', 'ASBES'], ['TILE', 'GENTENG'], ['METAL', 'SENG/METAL']]],
            'WATER_SOURCE' => ['Sumber Air Minum', [['PDAM', 'PDAM'], ['WELL', 'SUMUR'], ['OTHER', 'LAINNYA']]],
            'TOILET_TYPE' => ['Jenis Jamban', [['NONE', 'TIDAK PAKAI'], ['SQUAT', 'JAMBAN JONGKOK'], ['SEATED', 'JAMBAN DUDUK']]],
            'FECAL_DISPOSAL' => ['Jenis TPA Tinja', [['RIVER_SAWAH', 'KOLAM/SAWAH/SUNGAI'], ['SEPTIC', 'SEPTIC TANK'], ['OTHER', 'LAINNYA']]],
            'LIGHTING_SOURCE' => ['Sumber Penerangan', [['PLN', 'PLN'], ['NON_PLN', 'NON-PLN']]],
            'SETTLEMENT_CONDITION' => ['Kawasan Permukiman', [['KUMUH', 'KUMUH'], ['TIDAK_KUMUH', 'TIDAK KUMUH']]],
            'ROOM_FUNCTION' => ['Fungsi Ruang', [['PERUMAHAN', 'PERUMAHAN']]],
            'OWNERSHIP_STATUS' => ['Status Penguasaan Rumah', [['OWNED', 'MILIK SENDIRI'], ['RENTED', 'SEWA/KONTRAK'], ['OTHER', 'LAINNYA']]],
            'LAND_STATUS' => ['Status Penguasaan Tanah', [['OWNED', 'MILIK SENDIRI'], ['SIBLING', 'SAUDARA'], ['CHILD', 'ANAK'], ['PARENT', 'ORANG TUA']]],
        ];

        foreach ($cats as $code => [$name, $values]) {
            $cat = DB::table('master_categories')->where('code', $code)->first();
            $cid = $cat ? $cat->id : DB::table('master_categories')->insertGetId([
                'code' => $code,
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($values as $i => [$vcode, $label]) {
                DB::table('master_values')->updateOrInsert(
                    ['master_category_id' => $cid, 'code' => $vcode],
                    ['label' => $label, 'sort_order' => $i, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
