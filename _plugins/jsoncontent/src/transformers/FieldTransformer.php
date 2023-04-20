<?php

namespace thisisablock\jsoncontent\transformers;

use craft\base\Element;
use craft\elements\db\MatrixBlockQuery;
use craft\elements\db\TagQuery;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use thisisablock\jsoncontent\traits\MatrixBlockTransform;

class FieldTransformer
{
    use MatrixBlockTransform;

    static function transformEntryFields(Element $entry, $viewmode = "full", $allowedFields = [])
    {
        $fields = [];
        $fieldsLayout = $entry->getFieldLayout()->getFields();

        foreach ($fieldsLayout as $field) {
            if (!empty($allowedFields) && !in_array($field->handle, $allowedFields)) {
                continue;
            }
            $value = $entry->getFieldValue($field->handle);
            try {

                switch (get_class($field)) {
                    case 'craft\fields\Assets':
                        $fields[$field->handle] = AssetTransformer::transform($value, $viewmode);
                        break;
                    case 'craft\fields\Matrix':
                        $fields[$field->handle] = static::transformMatrix($field,
                            $value);
                        break;
                    case 'craft\fields\Tags':
                        $fields[$field->handle] = static::transformTags($value);
                        break;
                    case 'ether\seo\fields\SeoField':
                        break;
                    case 'craft\fields\Categories':
                        $categories = [];
                        foreach($value->all() as $category) {
                            $categories = CategoryTransformer::transform($category, $viewmode);
                        }
                        $fields[$field->handle] = $categories;
                        break;
                    default:
                        $fields[$field->handle] = $field->serializeValue($value,
                            $entry);
                        break;
                }
            } catch (\Exception $e) {
                dd('xxx', $e->getMessage());
            }

        }

        return $fields;
    }

    static function transformTags(TagQuery $query) {
        $tags = $query->all();
        $data = [];
        foreach ($tags as $tag) {
            $data[] = TagTransformer::transform($tag);
        }
        return $data;
    }

    static function transformMatrix($field, MatrixBlockQuery $query)
    {
        $blocks = $query->all();
        $relationData = [];

        $data = [];
        foreach ($blocks as $block) {
            $data[] = static::transformMatrixBlock($block, $relationData);
        }

        return $data;
    }
}
