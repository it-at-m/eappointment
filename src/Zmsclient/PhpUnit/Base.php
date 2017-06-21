<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsclient\PhpUnit;

use \Prophecy\Argument;

/**
 * @codeCoverageIgnore
 */
abstract class Base extends \BO\Slim\PhpUnit\Base
{
    /**
     * An array of API-Calls, e.g.:
     * [
     * [
     * 'function' => 'readGetResult',
     * 'url' => '/status/',
     * 'response' => '{}'
     * ],
     * ]
     */
    protected $apiCalls = array();

    public function setUp()
    {
        \App::$http = $this->getApiMockup();
        $this->sessionClass = new \BO\Zmsentities\Session();
        session_set_save_handler(new \BO\Zmsclient\SessionHandler(\App::$http), true);
    }

    public function tearDown()
    {
    }

    protected function getApiMockup()
    {
        $mock = $this->prophesize('BO\Zmsclient\Http');
        foreach ($this->getApiCalls() as $options) {
            $parameters = isset($options['parameters']) ? $options['parameters'] : null;
            $function = $options['function'];
            if ($function == 'readGetResult' || $function == 'readDeleteResult') {
                $function = $mock->__call(
                    $function,
                    [
                        $options['url'],
                        $parameters
                    ]
                );
            } elseif ($function == 'readPostResult') {
                $function = $mock->__call(
                    $function,
                    [
                        $options['url'],
                        Argument::type('\BO\Zmsentities\Schema\Entity'),
                        $parameters
                    ]
                );
            }
            $function->shouldBeCalled()
                ->willReturn(
                    new \BO\Zmsclient\Result(
                        $this->getResponse($options['response'], 200),
                        static::createBasicRequest()
                    )
                );
        }
        $api = $mock->reveal();
        return $api;
    }

    /**
     * Overwrite this function if api calls definition needs function calls
     */
    protected function getApiCalls()
    {
        return $this->apiCalls;
    }

    public function setApiCalls($apiCalls)
    {
        $this->apiCalls = $apiCalls;
        \App::$http = $this->getApiMockup();
    }
}
