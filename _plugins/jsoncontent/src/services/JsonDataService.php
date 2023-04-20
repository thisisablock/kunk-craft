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
use craft\helpers\ArrayHelper;
use \thisisablock\jsoncontent\JsonContent;

use Craft;
use craft\base\Component;
use League\Fractal\Manager;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\Serializer\SerializerAbstract;
use ReflectionFunction;
use yii\web\JsonResponseFormatter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author    thisisablock
 * @package   JsonContent
 * @since     0.1.0
 */
class JsonDataService extends Component
{
    
    
    function transformElementApiConfig($pattern) {

        $plugin = \craft\elementapi\Plugin::getInstance();
        $config = $plugin->getEndpoint($pattern);
        $request = Craft::$app->getRequest();
        $siteId = Craft::$app->getSites()->getCurrentSite()->id;
    
        if (is_callable($config)) {
            $params = Craft::$app->getUrlManager()->getRouteParams();
            $config = $this->_callWithParams($config, $params);
        }
        
        if (is_array($config)) {
            // Merge in the defaults
            $config = array_merge($plugin->getDefaultResourceAdapterConfig(), $config);
        }
    
        // Before anything else, check the cache
        $cache = ArrayHelper::remove($config, 'cache', false);
    
        if ($cache) {
            $cacheKey = 'elementapi:'.$siteId.':'.$request->getPathInfo().':'.$request->getQueryStringWithoutPath();
            $cacheService = Craft::$app->getCache();
        
            if (($cachedContent = $cacheService->get($cacheKey)) !== false) {
                // Set the JSON headers
                (new JsonResponseFormatter())->format($response);
            
                // Set the cached JSON on the response and return
                $response->format = Response::FORMAT_RAW;
                $response->content = $cachedContent;
                return $response;
            }
        }
    
        // Extract config settings that aren't meant for createResource()
        $serializer = ArrayHelper::remove($config, 'serializer');
        $jsonOptions = ArrayHelper::remove($config, 'jsonOptions', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $pretty = ArrayHelper::remove($config, 'pretty', false);
        $includes = ArrayHelper::remove($config, 'includes', []);
        $excludes = ArrayHelper::remove($config, 'excludes', []);
    
        // Generate all transforms immediately
        Craft::$app->getConfig()->getGeneral()->generateTransformsBeforePageLoad = true;
    
        // Get the data resource
        try {
            $resource = $plugin->createResource($config);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException($e->getMessage() ?: Craft::t('element-api', 'Resource not found'), 0, $e);
        }
    
        // Load Fractal
        $fractal = new Manager();
    
        // Serialize the data
        if (!$serializer instanceof SerializerAbstract) {
            switch ($serializer) {
                case 'dataArray':
                    $serializer = new DataArraySerializer();
                    break;
                case 'jsonApi':
                    $serializer = new JsonApiSerializer();
                    break;
                case 'jsonFeed':
                    $serializer = new JsonFeedV1Serializer();
                    break;
                default:
                    $serializer = new ArraySerializer();
            }
        }
    
        $fractal->setSerializer($serializer);
    
        // Parse includes/excludes
        $fractal->parseIncludes($includes);
        $fractal->parseExcludes($excludes);
    
        $data = $fractal->createData($resource);
        return $data->toArray();
    }
    
    private function _callWithParams($func, $params)
    {
        if (empty($params)) {
            return $func();
        }
        
        $ref = new ReflectionFunction($func);
        $args = [];
        
        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            
            if (isset($params[$name])) {
                if ($param->isArray()) {
                    $args[] = is_array($params[$name]) ? $params[$name] : [$params[$name]];
                } else if (!is_array($params[$name])) {
                    $args[] = $params[$name];
                } else {
                    return false;
                }
            } else if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                return false;
            }
        }
        
        return $ref->invokeArgs($args);
    }
}
