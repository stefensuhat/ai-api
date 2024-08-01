<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([UserScope::class])]
class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['role', 'content'];

    protected $casts = [
        'content' => 'json',
    ];

    public function chatGroup(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class);
    }

    public function log(): HasOne
    {
        return $this->hasOne(ChatLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditLoggable()
    {
        return $this->morphOne(UserCreditLog::class, 'loggable');
    }
}
