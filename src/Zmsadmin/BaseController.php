<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsadmin\Helper\ErrorHandler;

/**
 * @SuppressWarnings(Children)
 *
 */
abstract class BaseController extends \BO\Slim\Controller
{
    public static $errorHandler;

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
        self::$errorHandler = new ErrorHandler($request);
        self::$errorHandler->callingClass = (new \ReflectionClass(get_called_class()))->getShortName();
        return parent::__invoke($request, $response, $args);
    }
}
