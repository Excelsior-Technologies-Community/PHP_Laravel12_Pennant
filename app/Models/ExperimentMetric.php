<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperimentMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'experiment_name',
        'variant',
        'user_id',
        'converted',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'converted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
