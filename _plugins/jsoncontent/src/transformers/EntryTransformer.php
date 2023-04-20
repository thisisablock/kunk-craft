<?php

namespace thisisablock\jsoncontent\transformers;

use craft\elements\Asset;
use craft\elements\Entry;
use thisisablock\jsoncontent\transformers\sections\DatesTransformer;
use thisisablock\jsoncontent\transformers\sections\NewsTransformer;
use thisisablock\jsoncontent\transformers\sections\PageTransformer;

class EntryTransformer
{
    static function transform(Entry $entry, $viewmode = 'full')
    {
        if ($entry->type == 'news') {
            return NewsTransformer::transform($entry, $viewmode);
        }
        if ($entry->type == 'dates') {
            return DatesTransformer::transform($entry, $viewmode);
        }

        return PageTransformer::transform($entry, $viewmode);
    }

    static function getLinkable(Entry $entry) {
        return[
            'id' => $entry->id,
            'title' => $entry->title,
            'url' => $entry->url,
        ];
    }

}
