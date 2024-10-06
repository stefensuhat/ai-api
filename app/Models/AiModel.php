<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['name', 'type', 'version', 'preview_url'];

    public function scopeIsConversation($query)
    {
        return $query->where('type', 'conversation');
    }

    public function scopeVersion($query, $version)
    {
        return $query->where('version', $version);
    }
}
