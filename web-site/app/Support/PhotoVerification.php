<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PhotoVerification
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static function ensureColumns(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $columns = [
            'photo_verify_status' => fn ($t) => $t->string('photo_verify_status', 20)->nullable(),
            'photo_verify_selfie_url' => fn ($t) => $t->string('photo_verify_selfie_url', 500)->nullable(),
            'photo_verify_submitted_at' => fn ($t) => $t->timestamp('photo_verify_submitted_at')->nullable(),
            'photo_verify_reviewed_at' => fn ($t) => $t->timestamp('photo_verify_reviewed_at')->nullable(),
            'photo_verify_note' => fn ($t) => $t->string('photo_verify_note', 255)->nullable(),
            'profile_verified_at' => fn ($t) => $t->timestamp('profile_verified_at')->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn('users', $name)) {
                continue;
            }
            try {
                Schema::table('users', function ($table) use ($definition) {
                    $definition($table);
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public static function status(?User $user): ?string
    {
        if (! $user || ! Schema::hasColumn('users', 'photo_verify_status')) {
            return null;
        }

        $status = $user->photo_verify_status;

        return is_string($status) && $status !== '' ? $status : null;
    }

    public static function isVerified(User $user): bool
    {
        if ($user->is_verified) {
            return true;
        }

        if (Schema::hasColumn('users', 'profile_verified_at') && $user->profile_verified_at) {
            return true;
        }

        return self::status($user) === self::STATUS_APPROVED;
    }
}
