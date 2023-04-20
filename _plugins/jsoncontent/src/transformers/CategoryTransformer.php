<?php

namespace thisisablock\jsoncontent\transformers;

use craft\db\Paginator;
use craft\elements\Category;
use craft\elements\Entry;
use craft\web\twig\variables\Paginate;

class CategoryTransformer
{
    
    static function transform(Category $category, $viewmode = 'full', $context = null)
    {
        $data = [
            'type'    => 'category',
            'title'   => $category->title,
            'date'    => $category->dateCreated->format(\DateTime::ATOM),
            'url'     => $category->getUrl(),
        ];

        if ($viewmode === 'full') {
            $limit = 25;

            $query = Entry::find()->relatedTo($category)->orderBy('dateCreated desc');

            $paginator = new Paginator($query, [
                'pageSize' => $limit,
                'currentPage' => \Craft::$app->request->pageNum,
            ]);

            $entries = $paginator->getPageResults();

            $pageVar = Paginate::create($paginator);

            $data['pagination'] = [
                'first' => $pageVar->first,
                'last' => $pageVar->last,
                'total' => $pageVar->total,
                'currentPage' => $pageVar->currentPage,
                'totalPages' => $pageVar->totalPages,
                'nextUrl' => $pageVar->getNextUrl(),
                'prevUrl' => $pageVar->getPrevUrl(),
                'basePath' => $pageVar->getBasePath()
            ];

            foreach ($entries as &$entry) {
                $entry = EntryTransformer::transform($entry, 'teaser');
            }
            $data['entries'] = $entries;
            $data['fields'] = FieldTransformer::transformEntryFields($category, $viewmode);
        }

        return $data;
    }
}
