<?php

namespace thisisablock\jsoncontent\transformers;

use craft\elements\Entry;
use craft\helpers\UrlHelper;

class ArticleTransformer
{
    const allowedTeaserFields = [
        "articleHero",
        "articleMobileTeaserImage",
        "articleTeaserText",
        "articleCategory",
        "teaserViewmode",
        "articleSeo"
    ];


    static function transform(Entry $entry, $viewmode = 'full')
    {
        $data = [
            'id'    => $entry->id,
            'type'    => $entry->type->handle,
            'title'   => $entry->title,
            'postDate'    => isset($entry->postDate) ? $entry->postDate->format(\DateTime::ATOM) : null,
            'updateDate'    => isset($entry->dateUpdated) ? $entry->dateUpdated->format(\DateTime::ATOM) : null,
            'url'     => $entry->url,
            'jsonUrl' => UrlHelper::url("homepage/{$entry->id}.json"),
        ];

        if ($viewmode === 'full') {
            $data['author']  = UserTransformer::transform(
                $entry->author, 'teaser'
            );
            $next = $entry->getNext(['section' => 'article', 'orderBy' => 'postDate']);
            if ($next) {
                $data['next'] = [
                    'title' => $next->title,
                    'url' => $next->getUrl()
                ];
            }
            $prev = $entry->getPrev(['section' => 'article', 'orderBy' => 'postDate']);
            if ($prev) {
                $data['prev'] = [
                    'title' => $prev->title,
                    'url' => $prev->getUrl()
                ];
            }

            $data['fields'] = FieldTransformer::transformEntryFields($entry, $viewmode);
        }

        if ($viewmode === 'teaser') {
            $data['fields'] = FieldTransformer::transformEntryFields($entry, $viewmode, self::allowedTeaserFields);
        }

        if (!isset($data['fields'])) {
            $data['fields'] = FieldTransformer::transformEntryFields($entry, $viewmode);
        }

        return $data;
    }
}
