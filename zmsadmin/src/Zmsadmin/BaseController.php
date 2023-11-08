<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * @codeCoverageIgnore
     *
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }

    public function getSchemaConstraintList($schema)
    {
        $list = [];
        $locale = \App::$language->getLocale();
        foreach ($schema->properties as $key => $property) {
            if (isset($property['x-locale'])) {
                $constraints = $property['x-locale'][$locale];
                if ($constraints) {
                    $list[$key]['description'] = $constraints['messages'];
                }
            }
        }
        return $list;
    }
}
