<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'action' => $action,
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
        ]);
    }
}
