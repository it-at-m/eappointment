<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class SessionMiddleware
{
    const SESSION_ATTRIBUTE = 'session';

    private $sessionName;

    protected $sessionClass = null;

    public function __construct($name = 'default', $sessionClass = null)
    {
        $this->sessionName = $name;
        $this->sessionClass = $sessionClass;
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
            $session = Session\SessionData::getSessionFromName($sessionName);
            $session->setEntityClass($this->sessionClass);
            return $session;
        } catch (\Exception $exception) {
            throw  new \BO\Slim\Exception\SessionFailed();
        }
    }
}
