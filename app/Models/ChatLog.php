<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatLog extends Model
{
    use HasFactory;

    protected $fillable = ['model', 'input_tokens', 'output_tokens'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): HasOne
    {
        return $this->hasOne(Chat::class);
    }
}
