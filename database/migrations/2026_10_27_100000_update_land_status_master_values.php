<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $landCategory = DB::table('master_categories')->where('code', 'LAND_STATUS')->first();
        if (!$landCategory) {
            $catId = DB::table('master_categories')->insertGetId([
                'code' => 'LAND_STATUS',
                'name' => 'Status Penguasaan Tanah',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $catId = $landCategory->id;
        }

        // Pastikan opsi 'OTHER' dinonaktifkan dari form
        DB::table('master_values')
            ->where('master_category_id', $catId)
            ->where('code', 'OTHER')
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);

        // Opsi pilihan status penguasaan tanah: MILIK SENDIRI, SAUDARA, ANAK, ORANG TUA
        $options = [
            ['code' => 'OWNED',   'label' => 'MILIK SENDIRI', 'sort_order' => 0],
            ['code' => 'SIBLING', 'label' => 'SAUDARA',       'sort_order' => 1],
            ['code' => 'CHILD',   'label' => 'ANAK',          'sort_order' => 2],
            ['code' => 'PARENT',  'label' => 'ORANG TUA',      'sort_order' => 3],
        ];

        foreach ($options as $opt) {
            DB::table('master_values')->updateOrInsert(
                [
                    'master_category_id' => $catId,
                    'code' => $opt['code'],
                ],
                [
                    'label' => $opt['label'],
                    'sort_order' => $opt['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $landCategory = DB::table('master_categories')->where('code', 'LAND_STATUS')->first();
        if ($landCategory) {
            DB::table('master_values')
                ->where('master_category_id', $landCategory->id)
                ->whereIn('code', ['SIBLING', 'CHILD', 'PARENT'])
                ->delete();

            DB::table('master_values')
                ->where('master_category_id', $landCategory->id)
                ->where('code', 'OTHER')
                ->update(['is_active' => true]);
        }
    }
};
