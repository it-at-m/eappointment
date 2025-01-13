<?php

/**
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KGd
 *
 */

namespace BO\Zmscalldisplay;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Slim\Helper as SlimHelper;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    protected static $hashParameter = ['webcalldisplay' => ['collections', 'queue']];

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    protected function buildQuery(string $target, RequestInterface $request)
    {
        $queryArr  = [];
        $allParams = array_merge(static::$hashParameter[$target], ['template']);
        $currentQP = $request->getQueryParams();
        foreach ($allParams as $parameter) {
            if (isset($currentQP[$parameter]) && $currentQP[$parameter]) {
                $queryArr[$parameter] = $currentQP[$parameter];
            }
        }

        $queryArr['hmac'] = $this->buildHashFromParameterList($target, $request);

        return http_build_query($queryArr);
    }

    protected function buildHashFromParameterList(string $target, RequestInterface $request)
    {
        $paramsToHash = [];
        $currentQP = $request->getQueryParams();
        foreach (static::$hashParameter[$target] as $parameter) {
            if (isset($currentQP[$parameter]) && $currentQP[$parameter]) {
                $paramsToHash[$parameter] = $currentQP[$parameter];
            }
        }

        return SlimHelper::hashQueryParameters(
            $target,
            $paramsToHash,
            static::$hashParameter[$target]
        );
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     *
     * @param RequestInterface $request
     * @param array $parameters
     * @return string
     */
    protected function getWebcallDisplayUrl(RequestInterface $request, array $parameters)
    {
        $target = 'webcalldisplay';
        $config = \App::$http->readGetResult('/config/')->getEntity();

        $url = '' . $config->toProperty()->webcalldisplay->baseUrl->get();
        $url .= '?' . $this->buildQuery($target, $request);

        return $url;
    }
}
