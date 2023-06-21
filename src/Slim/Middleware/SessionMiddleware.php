<?php

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;

class SessionMiddleware
{
    const SESSION_ATTRIBUTE = 'session';

    protected $sessionClass = null;

    public function __construct($name = 'default', $sessionClass = null)
    {
        session_name($name);
        $this->sessionName = $name;
        $this->sessionClass = $sessionClass;
    }

    public function __invoke(
        ServerRequestInterface $requestInterface,
        RequestHandlerInterface $next
    ) {
        $sessionContainer = Session\SessionHuman::fromContainer(function () use ($requestInterface) {
            return $this->getSessionContainer($requestInterface);
        });

        if (null !== $next) {
            $requestInterface = $requestInterface->withAttribute(self::SESSION_ATTRIBUTE, $sessionContainer);
            $response = $next->handle($requestInterface);
        } else {
            $response = (new ResponseFactory())->createResponse();
        }

        return $response;
    }

    public function getSessionContainer($request)
    {
        $session = Session\SessionData::getSession($request);
        $session->setEntityClass($this->sessionClass);
        return $session;
    }
}
