<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}