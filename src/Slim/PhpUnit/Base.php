<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\PhpUnit;

use PHPUnit\Framework\TestCase;

use \BO\Slim\Middleware\SessionMiddleware;
use \BO\Slim\Middleware\Session\SessionHuman;
use \BO\Slim\Middleware\Session\SessionData;

/**
 * @SuppressWarnings(PHPMD)
 */
abstract class Base extends TestCase
{
    use \Helmich\Psr7Assert\Psr7Assertions;

    /**
      * Arguments for callback render
      *
      * @var Array $arguments
      */
    protected $arguments = array();

    /**
      * Parameters for the request
      *
      * @var Array $parameters
      */
    protected $parameters = array();

    /**
      * Data for the session
      *
      * @var Array $sessionData
      */
    protected $sessionData = array();

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
    protected function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function getRequest($method = 'GET', $uri = '', $sessionData = null)
    {
        if (null === $sessionData) {
            $sessionData = $this->getSessionData();
        }
        if (array_key_exists('human', $sessionData) && array_key_exists('ts', $sessionData['human'])) {
            // prevent isOveraged error-Handling
            $sessionData['human']['ts'] = time() - 10;
        }
        $request = self::createBasicRequest($method, $uri);
        $sessionContainer = SessionHuman::fromContainer(function () use ($sessionData) {
            $session = new SessionData($sessionData);
            $session->setEntityClass($this->sessionClass);
            return $session;
        });
        $request = $request->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $sessionContainer);
        return $request;
    }

    /**
     * Create a simple basic request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public static function createBasicRequest($method = "GET", $uri = '')
    {
        $request = \Slim\Http\Request::createFromEnvironment(\Slim\Http\Environment::mock([
            'REQUEST_METHOD'       => $method,
            'REQUEST_URI'          => $uri,
            'REMOTE_ADDR'          => '127.0.0.1'
        ]));
        $request = $request->withAttribute('ip_address', '127.0.0.1');
        return $request;
    }

    /**
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse($content = '', $status = 200, array $headers = array())
    {
        $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
        $headers = new \Slim\Http\Headers($headers);
        $response = new \Slim\Http\Response($status, $headers, $body);
        $body->write($content);
        return $response;
    }

    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters);
        $this->assertEquals(200, $response->getStatuscode());
        return $response;
    }

    protected function getControllerIdentifier()
    {
        $classname = (null === $this->classname) ?
            preg_replace('#^.*?(\w+)Test$#', '$1', get_class($this)) :
            $this->classname;
        $controllername = (false !== strpos($classname, '\\')) ? $classname : $this->namespace . $classname;
        return $controllername;
    }

    protected function render($arguments = [], $parameters = [], $sessionData = null, $method = 'GET')
    {
        $renderClass = $this->getControllerIdentifier();
        $controller = new $renderClass(\App::$slim->getContainer());

        //add uri to test multi languages
        $uri = (array_key_exists('__uri', $parameters)) ? $parameters['__uri'] : '';
        $request = $this->getRequest($method, $uri, $sessionData);
        $request = $this->setRequestParameters($request, $parameters, $method);
        $this->setValidatorInstance($parameters);
        $request = \BO\Slim\Middleware\Validator::withValidator($request);
        $response = $controller->__invoke($request, $this->getResponse(), $arguments);
        return $response;
    }

    protected function setRequestParameters($request, $parameters, $method)
    {
        if ('GET' === $method) {
            $request = $request->withQueryParams($parameters);
        } elseif ('POST' === $method) {
            $request = $request->withParsedBody($parameters);
        }
        if (array_key_exists('__body', $parameters)) {
            $body = new \Slim\Http\Body(fopen('php://temp', 'r+'));
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
