<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;

class HousePolicy
{
    /**
     * Admin dan Surveyor boleh melihat daftar rumah internal.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'surveyor',
        ]);
    }

    /**
     * Admin dan Surveyor boleh melihat detail rumah internal.
     */
    public function view(User $user, House $house): bool
    {
        return $user->hasAnyRole([
            'admin',
            'surveyor',
        ]);
    }

    /**
     * Hanya Admin yang boleh membuat data rumah.
     */
    public function create(User $user): bool
{
    return $user->hasAnyRole([
        'admin',
        'surveyor',
    ]);
}

    /**
     * Admin boleh mengubah semua data rumah.
     * Surveyor hanya boleh mengubah data rumah miliknya sendiri.
     */
    public function update(User $user, House $house): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('surveyor')) {
            return $house->created_by === $user->id;
        }

        return false;
    }

    /**
     * Hanya Admin yang boleh menghapus data rumah.
     */
    public function delete(User $user, House $house): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Belum menggunakan restore.
     */
    public function restore(User $user, House $house): bool
    {
        return false;
    }

    /**
     * Belum menggunakan permanent delete.
     */
    public function forceDelete(User $user, House $house): bool
    {
        return false;
    }
}