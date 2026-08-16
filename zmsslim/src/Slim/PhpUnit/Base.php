<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\PhpUnit;

use App;
use BO\Slim\Middleware\Validator;
use PHPUnit\Framework\TestCase;
use BO\Slim\Middleware\SessionMiddleware;
use BO\Slim\Middleware\Session\SessionHuman;
use BO\Slim\Middleware\Session\SessionData;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Headers;
use BO\Slim\Request;
use BO\Slim\Response;
use Slim\Psr7\Factory\StreamFactory;

/**
 * @SuppressWarnings(PHPMD)
 */
abstract class Base extends TestCase
{
    /**
      * Arguments for callback render
      *
      * @var array $arguments
      */
    protected $arguments = [];

    /**
      * Parameters for the request
      *
      * @var array $parameters
      */
    protected $parameters = [];

    /**
      * Data for the session
      *
      * @var array $sessionData
      */
    protected $sessionData = [];

    /**
     * Use this object instance for session getEntity()
     *
     * @var object|null
     */
    protected $sessionClass = null;

    /**
     * Namespace for tested classes
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * A class name if not detected automatically
     *
     * @var string|null
     */
    protected $classname = null;

    /**
     * Overwrite this function if session data needs function calls
     *
     */
    protected function getSessionData(): array
    {
        return $this->sessionData;
    }

    /**
     *
     * @param string $method
     * @param string $uri
     * @param array|null $sessionData
     *
     * @return ServerRequestInterface
     */
    protected function getRequest(
        string $method = 'GET',
        string $uri = '',
        ?array $sessionData = null
    ): ServerRequestInterface {
        if (null === $sessionData) {
            $sessionData = $this->getSessionData();
        }
        if (array_key_exists('human', $sessionData) && array_key_exists('ts', $sessionData['human'])) {
            // prevent isOveraged error-Handling
            $sessionData['human']['ts'] = time() - 10;
        }
        $request = self::createBasicRequest($method, $uri, ['Accept' => \BO\Slim\Headers::MEDIA_TYPE_TEXT_HTML]);
        $sessionContainer = SessionHuman::fromContainer(function () use ($sessionData) {
            $session = new SessionData($sessionData);
            $session->setEntityClass($this->sessionClass);
            return $session;
        });

        return $request->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $sessionContainer);
    }

    /**
     * Create a simple basic request
     *
     * @param string $method
     * @param string $uri
     * @return ServerRequestInterface
     */
    public static function createBasicRequest(
        string $method = "GET",
        string $uri = '',
        array $addHeaders = []
    ): ServerRequestInterface {
        $env = Environment::mock([
            'REQUEST_METHOD'       => $method,
            'REQUEST_URI'          => $uri,
            'REMOTE_ADDR'          => '127.0.0.1'
        ]);

        /** @psalm-suppress InternalMethod Slim's URI factory from mocked env is the supported test setup. */
        $uri = (new UriFactory())->createFromGlobals($env);
        $headers = Headers::createFromGlobals();
        foreach ($addHeaders as $key => $value) {
            $headers->addHeader($key, $value);
        }

        $body = (new StreamFactory())->createStream();

        $request = new Request($method, $uri, $headers, [], $env, $body, []);

        if (
            $method === 'POST' &&
            in_array($headers->getHeader('Content-Type'), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }

        return $request->withAttribute('ip_address', '127.0.0.1');
    }

    protected function getResponse(string $content = '', int $status = 200, array $headers = []): ResponseInterface
    {
        $body = (new StreamFactory())->createStream();
        $headers = new Headers($headers);
        $response = new Response($status, $headers, $body);
        $body->write($content);
        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters);
        $this->assertEquals(200, $response->getStatuscode());
        return $response;
    }

    protected function getControllerIdentifier(): string
    {
        if ($this->classname !== null && $this->classname !== '') {
            $classname = $this->classname;
        } else {
            $classname = preg_replace('#^.*?(\w+)Test$#', '$1', get_class($this));
            $classname = is_string($classname) ? $classname : '';
        }

        return str_contains($classname, '\\') ? $classname : $this->namespace . $classname;
    }

    protected function render(
        array $arguments = [],
        array $parameters = [],
        ?array $sessionData = null,
        string $method = 'GET'
    ): ResponseInterface {
        $renderClass = $this->getControllerIdentifier();
        if (!class_exists($renderClass)) {
            throw new \RuntimeException('Controller class ' . $renderClass . ' does not exist');
        }
        $container = App::$slim->getContainer();
        if (!$container instanceof \Psr\Container\ContainerInterface) {
            throw new \RuntimeException('Slim container is not initialized');
        }
        /** @var \BO\Slim\Controller $controller */
        /** @psalm-suppress UnsafeInstantiation */
        $controller = new $renderClass($container);

        //add uri to test multi languages
        $uri = (isset($parameters['__uri']) && is_string($parameters['__uri'])) ? $parameters['__uri'] : '';
        $request = $this->getRequest($method, $uri, $sessionData);
        $request = $this->setRequestParameters($request, $parameters, $method);
        $this->setValidatorInstance($parameters);
        $request = Validator::withValidator($request);

        $response = $controller->__invoke($request, $this->getResponse(), $arguments);
        if (!$response instanceof ResponseInterface) {
            throw new \RuntimeException('Controller did not return a PSR-7 response');
        }
        return $response;
    }

    protected function setRequestParameters(
        ServerRequestInterface $request,
        array $parameters,
        string $method
    ): ServerRequestInterface {
        if ('GET' === $method) {
            $request = $request->withQueryParams($parameters);
        } elseif ('POST' === $method) {
            $request = $request->withParsedBody($parameters);
        }
        if (array_key_exists('__body', $parameters)) {
            $body = (new StreamFactory())->createStream();
            $body->write($parameters['__body']);
            $request = $request->withBody($body);
        }
        if (array_key_exists('__cookie', $parameters)) {
            $request = $request->withCookieParams($parameters['__cookie']);
        }
        if (array_key_exists('__file', $parameters)) {
            $request = $request->withUploadedFiles($parameters['__file']);
        }
        if (array_key_exists('__header', $parameters)) {
            foreach ($parameters['__header'] as $key => $value) {
                $request = $request->withAddedHeader($key, $value);
            }
        }
        if (array_key_exists('__userinfo', $parameters)) {
            $request = $request->withUri($request->getUri()->withUserInfo(
                $parameters['__userinfo']['username'],
                $parameters['__userinfo']['password']
            ));
        }
        if (array_key_exists('__route', $parameters)) {
            $request = $request->withAttribute('route', $parameters['__route']);
        }

        return $request;
    }

    protected function setValidatorInstance(array $parameters): void
    {
        $validator = new \BO\Mellon\Validator($parameters);
        if (array_key_exists('__body', $parameters)) {
            $validator->setInput($parameters['__body']);
        }
        $validator->makeInstance();
    }

    public function assertRedirect(ResponseInterface $response, string $uri, int $status = 302): void
    {
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($uri, $response->getHeaderLine('Location'));
    }
}
