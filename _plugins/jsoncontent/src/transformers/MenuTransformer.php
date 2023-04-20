<?php

namespace thisisablock\jsoncontent\transformers;

use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\UrlHelper;

class MenuTransformer
{
    static function transform($menuItem)
    {
        $data = [];
        foreach ($menuItem as $item) {
            if (!is_null($item['entry_id'])) {
                $entry = Entry::find()
                    ->id($item['entry_id'])
                    ->one();

                if (is_null($entry)) {
                    $entry = Category::find()
                        ->id($item['entry_id'])
                        ->one();
                }

                if ($entry) {
                    $data[] = [
                        'name' => $item['name'],
                        'url' => $entry->url,
                        'target' => '_blank'
                    ];
                }
            } elseif (strlen($item['custom_url']) > 0) {
                $data[] = [
                    'name' => $item['name'],
                    'url' => $item['custom_url'],
                    'target' => '_blank'
                ];
            }
        }

        return $data;
    }
}
