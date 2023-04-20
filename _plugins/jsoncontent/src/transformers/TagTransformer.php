<?php

namespace thisisablock\jsoncontent\transformers;

use craft\elements\Tag;
use craft\helpers\UrlHelper;

class TagTransformer
{
    static function transform(Tag $entry, $viewmode = 'full')
    {
        $data = [
            'id' => $entry->id,
            'title'   => $entry->title,
            'url'     => $entry->getUrl(),
        ];
        //$data['fields'] = FieldTransformer::transformEntryFields($entry, $viewmode);
        return $data;
    }
}
