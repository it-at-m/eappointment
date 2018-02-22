<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \Psr\Http\Message\RequestInterface;

use \Psr\Http\Message\ResponseInterface;

use \BO\Mellon\Validator;

/**
 * @SuppressWarnings(NumberOfChildren)
 *
 */
abstract class BaseController extends Helper\Access
{
    public function __construct(\Interop\Container\ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $request = $this->initRequest($request);
        $this->initAccessRights($request);
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
}
