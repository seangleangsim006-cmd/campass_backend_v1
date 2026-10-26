<?php

namespace App\Support;

use App\Models\User;

class Premium
{
    public static function allows(?User $user): bool
    {
        // TODO: replace with a real subscription / purchase check when payments exist.
        // For now only staff can open premium destinations.
        return $user?->hasAnyRole(['super_admin', 'content_editor']) ?? false;
    }
}