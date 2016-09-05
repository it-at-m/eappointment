<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class SessionMiddleware
{
    const SESSION_ATTRIBUTE = 'session';

    private $sessionName;

    public function __construct($name = 'default')
    {
        $this->sessionName = $name;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public function __invoke(
        ServerRequestInterface $requestInterface,
        ResponseInterface $response,
        callable $next
    ) {
        $sessionContainer = Session\SessionHuman::fromContainer(function () {
            return $this->getSessionContainer($this->sessionName);
        });

        if (null !== $next) {
            $response = $next($requestInterface->withAttribute(self::SESSION_ATTRIBUTE, $sessionContainer), $response);
        }
        return $response;
    }

    public function getSessionContainer($sessionName = 'default')
    {
        try {
            return Session\SessionData::getSessionfromName($sessionName);
        } catch (\Exception $exception) {
            throw  new \BO\Slim\Exception('Es konnte leider keine Session ermittelt werden');
        }
    }
}
