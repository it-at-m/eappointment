<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\PhpUnit;

use \BO\Slim\Middleware\SessionMiddleware;
use \BO\Slim\Middleware\Session\SessionHuman;
use \BO\Slim\Middleware\Session\SessionData;

abstract class Base extends \PHPUnit_Framework_TestCase
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
            'REMOTE_ADDR'          => '127.0.0.1',
            'HTTP_COOKIE'          => 'Zmsappointment=unittest;', // fake session cookie
        ]));
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
        if (null === $this->classname) {
            $classname = get_class($this);
            $classname = preg_replace('#^.*?(\w+)Test$#', '$1', $classname);
        } else {
            $classname = $this->classname;
        }
        $controllername = "\BO\Zmsappointment\\$classname";
        return $controllername;
    }

    protected function render($arguments = [], $parameters = [], $sessionData = null)
    {
        $validator = new \BO\Mellon\Validator($parameters);
        $validator->makeInstance();
        $renderClass = $this->getControllerIdentifier();
        $controller = new $renderClass(\App::$slim->getContainer());
        $request = $this->getRequest('GET', '', $sessionData)->withQueryParams($parameters);
        $response = $controller->__invoke($request, $this->getResponse(), $arguments);
        return $response;
    }

    public function assertRedirect($response, $uri)
    {
        $this->assertResponseHasStatus($response, 302);
        $this->assertMessageHasHeaders($response, [
            'Location' => $uri,
        ]);
    }
}
