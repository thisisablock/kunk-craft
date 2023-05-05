<?php

namespace JsonExport\transformers;

use craft\elements\Category;
use craft\elements\Entry;
use League\Fractal\TransformerAbstract;

class MenuTransformer extends TransformerAbstract
{
    static function transform($menuItems)
    {
        $data = [];
        foreach ($menuItems as $item) {

            $toAdd = null;
            if (!is_null($item['entry_id']) && $item['entry_id'] !== 0) {
                $entry = Entry::find()
                    ->id($item['entry_id'])
                    ->one();

                if (is_null($entry)) {
                    $entry = Category::find()
                        ->id($item['entry_id'])
                        ->one();
                }

                if ($entry) {
                    $toAdd = [
                        'name' => $item['name'],
                        'url' => $entry->url,
                        'target' => '_blank',
                        'children' => []
                    ];
                }
            } elseif (strlen($item['custom_url']) > 0) {
                $toAdd = [
                    'name' => $item['name'],
                    'url' => $item['custom_url'],
                    'target' => '_blank',
                    'children' => []
                ];
            }

            if (!$toAdd) {
                continue;
            }

            if (
                isset($item['children']) &&
                is_array($item['children']) &&
                count($item['children']) > 0
            ) {
                $toAdd['children'] = static::transform($item['children']);
            }

            $data[] = $toAdd;
        }
        return $data;
    }
}
