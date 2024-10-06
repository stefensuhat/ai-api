<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'value',
    ];

    public function scopeOfKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function chatGroup(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatGroup::class);
    }
}
