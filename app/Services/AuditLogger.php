<?php

namespace App\Services;

use App\Models\User;
use App\Support\DataMasker;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log(?User $user, string $action, string $type, ?int $id, ?array $old = null, ?array $new = null): void
    {
        $sanitizedOld = $old ? self::sanitizePayload($old) : null;
        $sanitizedNew = $new ? self::sanitizePayload($new) : null;

        DB::table('audit_logs')->insert([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $type,
            'auditable_id' => $id,
            'old_values' => $sanitizedOld ? json_encode($sanitizedOld) : null,
            'new_values' => $sanitizedNew ? json_encode($sanitizedNew) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function sanitizePayload(array $data): array
    {
        if (isset($data['nik'])) {
            $data['nik'] = DataMasker::maskNik($data['nik']);
        }

        if (isset($data['phone'])) {
            $data['phone'] = DataMasker::maskPhone($data['phone']);
        }

        return $data;
    }
}
