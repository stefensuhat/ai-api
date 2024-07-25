<?php

namespace App\Enums;

enum AspectRatio: string
{
    case square = 'square';
    case wide = 'wide';
    case portrait = 'portrait';

    public function getSize(): array
    {
        return match ($this) {
            self::square => ['width' => 1024, 'height' => 1024],
            self::wide => ['width' => 1024, 'height' => 576],
            self::portrait => ['width' => 576, 'height' => 1024],
            default => 'Unknown'
        };
    }
}
