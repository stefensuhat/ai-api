<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserCreditLog extends Model
{
    use HasFactory;

    public function userCredit(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UserCredit::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
