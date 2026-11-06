<?php

$styles = [];

for ($number = 1; $number <= 14; $number++) {
    $id = sprintf('%02d', $number);
    $styles["card_{$id}"] = [
        'name' => "Card {$id}",
        'bg_image' => sprintf('card_%02d.png', min($number, 3)),
        'logo' => '/storage/images/site_logo.png',
        'bg_color' => 'blue',
        'font_color' => 'white',
        'font_style' => 'font-family:sans-serif;color:' . ($number === 1 ? '#c24641' : 'white'),
    ];
}

return $styles;
