<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class FeatureAudit extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'feature',
        'action',
        'ip_address',
        'user_agent',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /**
     * Helper to log audit actions.
     */
    public static function logAction(
        string $featureName,
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $targetName = null,
        ?string $details = null
    ): self {
        $actionSummary = $action;
        if ($targetName) {
            $actionSummary .= " [{$targetName}]";
        }
        if ($details && $details !== $targetName) {
            $actionSummary .= " - {$details}";
        }

        return static::create([
            'admin_id' => Auth::id(),
            'user_id' => $targetId,
            'feature' => $featureName,
            'action' => substr($actionSummary, 0, 255),
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => substr(Request::userAgent() ?? 'CLI / Browser', 0, 500),
        ]);
    }
}