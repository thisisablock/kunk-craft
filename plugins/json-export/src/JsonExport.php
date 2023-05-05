<?php

namespace JsonExport;

use Craft;
use craft\base\Plugin;
use craft\elementapi\ElementTransformer;
use JsonExport\transformers\MenuTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use olivestudio\olivemenus\Olivemenus;
use thisisablock\jsoncontent\transformers\UserTransformer;

/**
 * JsonExport plugin
 *
 * @method static JsonExport getInstance()
 */
class JsonExport extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [],
        ];
    }

    public function init()
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });

        Craft::$app->view->hook('jsonData', function(array &$context) {

            $resource = new Item($context['entry'], new ElementTransformer(), null);

            $fractal = new Manager();
            $serializer = new ArraySerializer();

            $fractal->setSerializer($serializer);

            // Parse includes/excludes
//            $fractal->parseIncludes($includes);
//            $fractal->parseExcludes($excludes);

            $entry = $fractal->createData($resource);

            $data = [
                'entry' => $entry->toArray(),
                'menu' => $this->getMenus()
            ];

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
        $olive = Olivemenus::getInstance();
        $menus = $olive->olivemenus->getAllMenus(1);
        $data = [];
        foreach ($menus as $menu) {
            $items = $olive->olivemenuItems->getMenuItems($menu->id);
            $fractal = new Manager();
            $resource = new Item($items, new MenuTransformer(), null);
            $menuResource = $fractal->setSerializer(new ArraySerializer())->createData($resource);
            $data[$menu->handle] = $menuResource->toArray();
        }
        return $data;
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
