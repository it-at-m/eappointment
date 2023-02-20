<?php
/**
 * HTTP Basic Authentication
 *
 * inspired by https://github.com/codeguy/Slim-Extras/blob/master/Middleware/HttpBasicAuth.php
 *
 * Usage:
 *   \App::httpBasicAuth['username'] = password_hash('password', PASSWORD_DEFAULT);
 *   // better pre-calculate hash in the config with `php -r "echo password_hash('password', PASSWORD_DEFAULT);"`
 *   \App::$slim->add(new \BO\Slim\Middleware\HttpBasicAuth(\BO\Slim\Middleware\HttpBasicAuth::useAppConfig());
 */

namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;

class HttpBasicAuth
{
    /**
     * @var string
     */
    protected $realm;

    /**
     * @var Callable
     */
    protected $isAuthorized;

    public function __construct(callable $isAuthorized, $realm = null)
    {
        $this->isAuthorized = $isAuthorized;
        $this->realm = $realm ?: "Password ".\App::IDENTIFIER;
    }

    public static function useAppConfig(): callable
    {
        return function ($authUser, $authPass) {
            if (!count(\App::$httpBasicAuth)) {
                return true;
            }
            if (isset(\App::$httpBasicAuth[$authUser]) && password_verify($authPass, \App::$httpBasicAuth[$authUser])) {
                return true;
            }
            return false;
        };
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $serverParams = $request->getServerParams();
        $authUser = $serverParams['PHP_AUTH_USER'] ?? '';
        $authPass = $serverParams['PHP_AUTH_PW'] ?? '';

        if ($this->isAuthorized->call($this, $authUser, $authPass)) {
            $response = $next->handle($request);
        } else {
            $response = (new ResponseFactory())->createResponse(401, 'Unauthorized');
            $response = $response->withHeader('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
        }

        return $response;
    }
}
