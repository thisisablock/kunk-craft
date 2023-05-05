<?php

use craft\elements\Entry;
use craft\helpers\UrlHelper;

return [
    'endpoints' => [
        'page.json' => function() {
            return [
                'elementType' => Entry::class,
                'criteria' => ['section' => 'page'],
                'transformer' => function(Entry $entry) {
                    return [
                        'title' => $entry->title,
                        'url' => $entry->url
                    ];
                },
            ];
        },
        'page/<entryId:\d+>.json' => function($entryId) {
            return [
                'elementType' => Entry::class,
                'criteria' => ['id' => $entryId],
                'one' => true,
                'cache' => false
            ];
        },
    ]
];
