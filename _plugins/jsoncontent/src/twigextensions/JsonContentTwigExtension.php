<?php
/**
 * JsonContent plugin for Craft CMS 3.x
 *
 * -,-
 *
 * @link      https://thisisablock.com
 * @copyright Copyright (c) 2019 thisisablock
 */
namespace thisisablock\jsoncontent\twigextensions;

use craft\elements\Entry;
use modules\transformers\EntryTransformer;

/**
 * @author    thisisablock
 * @package   JsonContent
 * @since     0.1.0
 */
class JsonContentTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'JsonContent';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
      return [
        new \Twig_SimpleFunction('jsonData', [$this, 'handleJsonData']),
      ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('jsonEntry', [$this, 'handleEntry']),
        ];
    }

    /**
     * @param null $text
     *Ø
     * @return string
     */
    public function handleEntry(Entry $entry)
    {
        return '<script id="data" type="application/json">'.json_encode(\thisisablock\jsoncontent\transformers\EntryTransformer::transform($entry)).'</script>';
    }
}
