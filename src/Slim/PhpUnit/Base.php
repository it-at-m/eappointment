<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\PhpUnit;

use App;
use BO\Slim\Middleware\Validator;
use Helmich\Psr7Assert\Psr7Assertions;
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
    use Psr7Assertions;

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
     * @var Object $sessionClass
     */
    protected $sessionClass = null;

    /**
     * Namespace for tested classes
     */
    protected $namespace = '';

    /**
     * A class name if not detected automatically
     *
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

        $uri = (new UriFactory())->createFromGlobals($env);
        $headers = Headers::createFromGlobals();
        foreach ($addHeaders as $key => $value) {
            $headers->addHeader($key, $value);
        }

        $body = (new StreamFactory())->createStream();

        $request = new Request($method, $uri, $headers, [], $env, $body, []);

        if ($method === 'POST' &&
            in_array($headers->getHeader('Content-Type'), ['application/x-www-form-urlencoded', 'multipart/form-data'])
        ) {
            // parsed body must be $_POST
            $request = $request->withParsedBody($_POST);
        }

        return $request->withAttribute('ip_address', '127.0.0.1');
    }

    /**
     *
     * @return ResponseInterface
     */
    protected function getResponse($content = '', $status = 200, array $headers = [])
    {
        $body = (new StreamFactory())->createStream();
        $headers = new Headers($headers);
        $response = new Response($status, $headers, $body);
        $body->write($content);
        return $response;
    }

    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters);
        $this->assertEquals(200, $response->getStatuscode());
        return $response;
    }

    protected function getControllerIdentifier(): string
    {
        $classname = (null === $this->classname) ?
            preg_replace('#^.*?(\w+)Test$#', '$1', get_class($this)) :
            $this->classname;

        return (false !== strpos($classname, '\\')) ? $classname : $this->namespace . $classname;
    }

    protected function render(
        array $arguments = [],
        $parameters = [],
        $sessionData = null,
        $method = 'GET'
    ) {
        $renderClass = $this->getControllerIdentifier();
        /** @var \BO\Slim\Controller $controller */
        $controller = new $renderClass(App::$slim->getContainer());

        //add uri to test multi languages
        $uri = (array_key_exists('__uri', $parameters)) ? $parameters['__uri'] : '';
        $request = $this->getRequest($method, $uri, $sessionData);
        $request = $this->setRequestParameters($request, $parameters, $method);
        $this->setValidatorInstance($parameters);
        $request = Validator::withValidator($request);

        return $controller->__invoke($request, $this->getResponse(), $arguments);
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

    protected function setValidatorInstance($parameters)
    {
        $validator = new \BO\Mellon\Validator($parameters);
        if (array_key_exists('__body', $parameters)) {
            $validator->setInput($parameters['__body']);
        }
        $validator->makeInstance();
    }

    public function assertRedirect($response, $uri, $status = 302)
    {
        $this->assertResponseHasStatus($response, $status);
        $this->assertMessageHasHeaders($response, [
            'Location' => $uri,
        ]);
    }
}
