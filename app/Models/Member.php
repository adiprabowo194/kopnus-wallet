<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'member_code',
        'name',
        'email',
        'phone',
        'status',
        'balance',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
