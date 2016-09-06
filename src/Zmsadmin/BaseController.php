<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsadmin\Helper\ErrorHandler;
use BO\Zmsclient\SessionHandler;
use BO\Zmsclient\Auth;

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
        self::$errorHandler = new ErrorHandler();
        self::$errorHandler->callingClass = (new \ReflectionClass(get_called_class()))->getShortName();

        parent::__construct($containerInterface);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return parent::__invoke($request, $response, $args);
    }
}
