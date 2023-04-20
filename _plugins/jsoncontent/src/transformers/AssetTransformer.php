<?php

namespace thisisablock\jsoncontent\transformers;

use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use function Arrayy\array_last;

class AssetTransformer
{
    const queries = [
        'sm' => '576',
        'md' => '768',
        'lg' => '1024',
        'xl' => '1280'
    ];

    static function transform(AssetQuery $query, string $viewmode) {
        $values = $query->all();
        $data = [];
        /** @var \craft\elements\Asset $value */
        foreach ($values as $value) {
            $data[] = static::transformAsset($value, $viewmode);
        };
        return $data;
    }

    static function transformAsset(Asset $asset, string $viewmode) {

        $data = [
            'focalpoint' => $asset->focalPoint,
            'filename' => $asset->filename,
            'title' => $asset->title,
            'url' => $asset->url,
            'fullUrl' => $asset->url,
            'thumbUrl' => $asset->getUrl('thumb'),
            'srcset' => null,
            'height' => $asset->height,
            'width' => $asset->width
        ];

        if (isset($asset->alt)) {
            $data['alt'] = $asset->alt;
        }

        if (isset($asset->caption)) {
            $data['caption'] = $asset->caption;
        }

         $rImage = self::responsiveImage($asset, $viewmode);
         if (!empty($rImage)) {
             $data['url'] = array_last($rImage)['url'];

             $srcset = [];
             foreach ($rImage as $img) {
                 $srcset[] = $img['url'] . ' '.$img['size'];
             }
             $data['srcset'] = join(', ', $srcset);
         }

         return $data;
    }

    private static function responsiveImage(Asset $asset, string $viewmode)
    {
        $data = [];

        switch ($asset->folderPath) {
            case "hero/":
                $data = self::getDefaultSet($asset);
                if ($viewmode === 'full') {
                    $data['lg'] = $asset->getUrl(
                        [
                            'mode' => 'crop',
                            'width' => 1180,
                            'position' => 'center-center',
                            'quality' => 82
                        ]
                    );
                }
                break;
        }

        if (empty($data)) return [];

        $srcset = [];
        foreach ($data as $queryType => $url) {
            $queries = self::queries;
            $srcset[] = [
                'url' => $url,
                'size' => $queries[$queryType].'w'
            ];
        }

        return $srcset;
    }


    public static function getDefaultSet(Asset $asset) {
        $data = [];
        $data['sm'] = $asset->getUrl(
            [
                'mode' => 'crop',
                'width' => 576,
                'position' => 'center-center',
                'quality' => 82
            ]
        );
        $data['md'] = $asset->getUrl(
            [
                'mode' => 'crop',
                'width' => 786,
                'position' => 'center-center',
                'quality' => 82
            ]
        );
        $data['lg'] = $asset->getUrl(
            [
                'mode' => 'crop',
                'width' => 1180,
                'position' => 'center-center',
                'quality' => 82
            ]
        );
        return $data;
    }

}
