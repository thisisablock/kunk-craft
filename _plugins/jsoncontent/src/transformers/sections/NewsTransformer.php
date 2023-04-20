<?php

namespace thisisablock\jsoncontent\transformers\sections;

use craft\elements\Entry;
use craft\helpers\UrlHelper;
use thisisablock\jsoncontent\transformers\FieldTransformer;

class NewsTransformer
{
    static function transform(Entry $entry, $viewmode = 'full')
    {
        $data = [
            'type'    => $entry->type->handle,
            'title'   => $entry->title,
            'url'     => $entry->url,
            'jsonUrl' => UrlHelper::url("homepage/{$entry->id}.json"),
        ];
        $data['fields'] = FieldTransformer::transformEntryFields($entry, $viewmode);
        return $data;
    }
}
