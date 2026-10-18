<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchBillingPrintSetting extends Model
{
    protected $fillable = [
        'store_id',
        'overrides',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'store_id' => 'integer',
            'overrides' => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
