<?php

namespace thisisablock\jsoncontent\transformers;

use craft\db\Paginator;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\Paginate;

class UserTransformer
{
    static function transform(User $user, $viewmode = 'full')
    {
        $data = [
            'url' => $user->enabled && !$user->suspended ? '/autoren/' . $user->username : null,
            'username' => $user->username,
            'name' => $user->firstName . ' ' . $user->lastName,
            'userbio' => $user->userBio,
            'photo' => $user->photo ? AssetTransformer::transformAsset($user->photo, $viewmode) : '',
        ];

        if ($viewmode === 'full') {

            $limit = 25;
            $query = Entry::find()->type('Article')->authorId($user->id)->orderBy('dateCreated desc');

            $paginator = new Paginator($query, [
                'pageSize' => $limit,
                'currentPage' => \Craft::$app->request->pageNum,
            ]);

            $entries = $paginator->getPageResults();

            foreach ($entries as &$entry) {
                $entry = EntryTransformer::transform($entry, 'teaser');
            }

            $data['entries'] = $entries;

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
        }


        return $data;
    }
}
