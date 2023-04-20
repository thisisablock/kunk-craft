<?php
/**
 * JsonContent plugin for Craft CMS 3.x
 *
 * json for frontend
 *
 * @link      thisisablock.com
 * @copyright Copyright (c) 2020 thisisablock
 */

namespace thisisablock\jsoncontent;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use thisisablock\jsoncontent\services\AlgoliaDataService;
use \thisisablock\jsoncontent\services\JsonDataService as JsonDataService;
use thisisablock\jsoncontent\transformers\CategoryTransformer;
use thisisablock\jsoncontent\transformers\ErrorTransformer;
use thisisablock\jsoncontent\transformers\MenuTransformer;
use thisisablock\jsoncontent\transformers\EntryTransformer;
use thisisablock\jsoncontent\transformers\UserTransformer;
use \thisisablock\jsoncontent\twigextensions\JsonContentTwigExtension;
use yii\base\Event;

/**
 * Class JsonContent
 *
 * @author    thisisablock
 * @package   JsonContent
 * @since     0.2
 *
 */
class JsonContent extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var JsonContent
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.2';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new JsonContentTwigExtension());

        $this->setComponents([
            'dataService' => JsonDataService::class,
            'algoliaService' => AlgoliaDataService::class
        ]);

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::$app->view->hook('jsonData', function(array &$context) {

            $menus = $this->getMenus();

            $data = [];
            if (isset($context['entry'])) {
                $data['entry'] = EntryTransformer::transform($context['entry']);
            } else if (isset($context['category'])) {
                $data['entry'] = CategoryTransformer::transform($context['category'], 'full', $context);
            } else {
                $data['error'] = ErrorTransformer::transform();
            }

            if ($menus) {
                $data['menu'] = $menus;
            }

            return '<script id="data" type="application/json">'.json_encode($data).'</script>';
        });

        Craft::$app->view->hook('jsonUserData', function(array &$context) {
            $user = $context['user'];
            $menus = $this->getMenus();
            $data = [];
            $data['entry'] = UserTransformer::transform($user);
            $data['entry']['type'] = 'user';

            if (count($menus) > 0) {
                $data['menu'] = $menus;
            }

            return '<script id="data" type="application/json">'.json_encode($data).'</script>';
        });
    }

    protected function getMenus() {
        $menuPlugin = \olivestudio\olivemenus\Olivemenus::getInstance();
        $menu = $menuPlugin->olivemenus->getMenuByHandle('mainMenu');
        $footerMenu = $menuPlugin->olivemenus->getMenuByHandle('footerMenu');

        $data = [];

//        $data['mainMenu'] = MenuTransformer::transform(
//            $menuPlugin->olivemenuItems->getMenuItems($menu->id)
//        );
//        $data['footerMenu'] = MenuTransformer::transform(
//            $menuPlugin->olivemenuItems->getMenuItems($footerMenu->id)
//        );

        return $data;
    }

    // Protected Methods
    // =========================================================================

}
