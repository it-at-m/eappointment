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

    protected $workstation;

    public function __construct(\Interop\Container\ContainerInterface $containerInterface)
    {
        $this->workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        parent::__construct($containerInterface);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}
