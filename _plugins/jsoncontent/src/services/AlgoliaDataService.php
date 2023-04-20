<?php
/**
 * JsonContent plugin for Craft CMS 3.x
 *
 * -,-
 *
 * @link      https://thisisablock.com
 * @copyright Copyright (c) 2019 thisisablock
 */

namespace thisisablock\jsoncontent\services;

use craft\elementapi\DataEvent;
use craft\elementapi\JsonFeedV1Serializer;
use craft\elements\Entry;
use craft\base\Component;
use thisisablock\jsoncontent\transformers\ArticleTransformer;

/**
 * @author    thisisablock
 * @package   JsonContent
 * @since     0.1.0
 */
class AlgoliaDataService extends Component
{
    function preprocessArticles($articles) {
        return array_map(function($article){
            return $this->preprocessArticle($article);
        }, $articles);
    }

    function preprocessArticle(Entry $entry) {
        $a = ArticleTransformer::transform($entry);

        $data = [
            'id' => $a['id'],
            'title' => $a['title'],
            'url' => $a['url'],
            'author_name' => $a['author']['name'],
            'author_url' => $a['author']['url'],
            'photo' => ''
        ];

        if (!empty($a['fields']['articleHero'])) {
            $data['photo'] = $a['fields']['articleHero'][0]['fullUrl'];
        }

        $chapters = [];
        $description = false;

        $tags = [];
        if (!empty($a['fields']['articleTags'])) {
            $tags = array_map(function($tag) {
                return $tag['title'];
            }, $a['fields']['articleTags']);
        }

        foreach ($a['fields']['articleBlocks'] as $block) {
            if ($block['type'] == 'text' && !$description) {
                $description = $block['fields']['textBody'];
            }
            if ($block['type'] == 'tocChapter') {
                $chapters[] = $block['fields']['tocChapterName'];
            }
        }

        $data['chapters'] = $chapters;
        if ($description) {
            $data['description'] = $description;
        }
        $data['tags'] = $tags;

        return $data;
    }
}
