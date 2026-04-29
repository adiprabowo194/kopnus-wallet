<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'reference_number',
        'member_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'description',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
