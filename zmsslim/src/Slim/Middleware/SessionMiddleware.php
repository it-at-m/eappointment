<?php

namespace BO\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware
{
    const string SESSION_ATTRIBUTE = 'session';

    protected string $sessionName;

    protected ?object $sessionClass = null;

    /** @psalm-api Instantiated by Slim middleware registration. */
    public function __construct(string $name = 'default', ?object $sessionClass = null)
    {
        session_name($name);
        $this->sessionName = $name;
        $this->sessionClass = $sessionClass;
    }

    public function __invoke(
        ServerRequestInterface $requestInterface,
        RequestHandlerInterface $next
    ): ResponseInterface {
        $sessionContainer = Session\SessionHuman::fromContainer(function () use ($requestInterface) {
            return $this->getSessionContainer($requestInterface);
        });

        $requestInterface = $requestInterface->withAttribute(self::SESSION_ATTRIBUTE, $sessionContainer);
        return $next->handle($requestInterface);
    }

    public function getSessionContainer(ServerRequestInterface $request): Session\SessionData
    {
        $session = Session\SessionData::getSession($request);
        $session->setEntityClass($this->sessionClass);
        return $session;
    }
}
