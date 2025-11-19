<?php
return [
    'okaz' => [
        'slug' => 'okaz',
        'active' => true,
        'pattern_type' => 'date',
        'pattern_template' => 'https://www.okaz.com.sa/digitals/{YYYY}/{MM}/{DD}/index.html',
    ],
    'arabnews' => [
        'slug' => 'arabnews',
        'active' => true,
        'pattern_type' => 'sequence',
        'current_issue' => '50314',
        'pattern_template' => 'https://www.arabnews.com/sites/default/files/pdf/{ISSUE_ID}/index.html',
    ],
    'ring' => [
        'slug' => 'ring',
        'active' => true,
        'pattern_type' => 'monthly',
        'pattern_template' => 'https://ringmagazine.com/en/magazines/{MM_slug}_{YYYY}/view',
    ],
];
