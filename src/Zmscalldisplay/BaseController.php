<?php
/**
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KGd
 *
 */
namespace BO\Zmscalldisplay;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use BO\Slim\Helper as SlimHelper;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    static $hashParameter = ['webcalldisplay' => ['collections', 'queue']];

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    public function buildQuery(RequestInterface $request)
    {
        $firstKey = array_key_first(static::$hashParameter);
        $queryArr = [];
        foreach(array_values(static::$hashParameter[$firstKey]) as $parameter) {
            if ($request->getQueryParam($parameter)) {
                $queryArr[$parameter] = $request->getQueryParam($parameter);
            }
        }        
        if ($request->getQueryParam('template')) {
            $queryArr['template'] = $request->getQueryParam('template');
        }
        $queryArr['hmac'] = $this->buildHashFromParameterList($request);
        return http_build_query($queryArr);
    }

    public function buildHashFromParameterList(RequestInterface $request)
    {
        $firstKey = array_key_first(static::$hashParameter);
        $paramsToHash = [];
        foreach(array_values(static::$hashParameter[$firstKey]) as $parameter) {
            if ($request->getQueryParam($parameter)) {
                $paramsToHash[$parameter] = $request->getQueryParam($parameter);
            }
        }
        $hash = SlimHelper::hashQueryParameters(
            $firstKey,
            $paramsToHash,
            static::$hashParameter[$firstKey]
        );
        return $hash;
    }

    protected function getHashedUrl(RequestInterface $request, array $parameters)
    {
        $urlParamName = array_key_first(static::$hashParameter);
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $parameters[$urlParamName] = '';
        $parameters[$urlParamName] .= $config->toProperty()->webcalldisplay->baseUrl->get();
        $parameters[$urlParamName] .= '?'. $this->buildQuery($request);
        return $parameters;
    }

}
