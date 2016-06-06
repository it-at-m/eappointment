<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(Children)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     * @return self
     */
    public function __construct(\Interop\Container\ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}
