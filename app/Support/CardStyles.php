<?php

namespace App\Support;

use App\Models\Config;

class CardStyles
{
    public static function presets(): array
    {
        $configured = Config::item('card_styles');

        return array_replace(is_array($configured) ? $configured : [], config('card_styles'));
    }
}
