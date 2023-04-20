<?php

namespace thisisablock\jsoncontent\traits;

use thisisablock\jsoncontent\transformers\AssetTransformer;
use thisisablock\jsoncontent\transformers\CategoryTransformer;
use thisisablock\jsoncontent\transformers\EntryTransformer;
use thisisablock\jsoncontent\transformers\sections\DatesTransformer;
use thisisablock\jsoncontent\transformers\sections\NewsTransformer;
use craft\elements\Entry;

trait MatrixBlockTransform {
    static function transformMatrixBlock($block, &$relationData = []): array
    {
        $fieldData = $block->getSerializedFieldValues();
        $fieldsLayout = $block->getFieldLayout()->getFields();

        switch ($block->type->handle) {
            case 'dynamicBlock':
                switch ($block->dynamicBlockTemplate->value) {
                    case "newsAndDates":
                        $news = Entry::find()->type('News')->all();
                        $dates = Entry::find()->type('Dates')->all();

                        $data = [
                            'news' => Entry::find()->type('News')->all(),
                            'dates' => Entry::find()->type('Dates')->all()
                        ];
                        foreach ($data['news'] as $k => $entry) {
                            $data['news'][$k] = NewsTransformer::transform($entry);
                        }

                        foreach ($data['dates'] as $k => $entry) {
                            $data['dates'][$k] = DatesTransformer::transform($entry);
                        }

                        $fieldData[$block->dynamicBlockTemplate->value] = [
                            'entries' => $data,
                        ];
                        break;
                    case "datelist":
                        $dates = Entry::find()->type('Dates')->all();
                        $data = [
                            'dates' => Entry::find()->type('Dates')->all(),
                        ];
                        foreach ($data['dates'] as $k => $entry) {
                            $data['dates'][$k] = DatesTransformer::transform($entry);
                        }

                        $fieldData[$block->dynamicBlockTemplate->value] = [
                            'entries' => $data,
                        ];
                        break;
                }
                break;
            default:
                break;
        }

        // check for relations outside specific cases
        foreach ($fieldsLayout as $k => $layout) {
            switch (get_class($layout)) {
                case 'craft\fields\Categories':
                    if (isset($fieldData[$layout->handle]['entries'])) break;
                    $categories = $block[$layout->handle]->all();
                    foreach ($categories as &$category) {
                        $category = CategoryTransformer::transform($category, 'teaser');
                    }
                    $fieldData[$layout->handle]['entries'] = $categories;
                    break;
                case 'craft\fields\Assets':
                    if (isset($fieldData[$layout->handle]['entries'])) break;
                    $fieldData[$layout->handle] = AssetTransformer::transform($block[$layout->handle], 'full');
                    break;
                case 'craft\fields\Tags':
                    $fieldData[$layout->handle] = static::transformTags($block[$layout->handle]);
                    break;
                case 'craft\fields\Entries':
                    if (isset($fieldData[$layout->handle]['entries'])) break;

                    $entries = $block[$layout->handle]->all();
                    foreach ($entries as &$entry){
                        $entry = EntryTransformer::getLinkable($entry);
                    }
                    $fieldData[$layout->handle] = $entries;
                    break;
                case 'verbb\supertable\fields\SuperTableField':
                    $entries = $block[$layout->handle]->all();
                    $tableData = [];
                    foreach ($entries as $entry) {
                        $tableData[] = static::transformMatrixBlock($entry);
                    }
                    $fieldData[$layout->handle] = $tableData;
                    break;
            }
        }

        return [
            'type'   => $block->type->handle,
            'fields' => $fieldData,
        ];
    }

}
