<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsmessaging\PhpUnit;

use \Prophecy\Argument;

/**
 * @codeCoverageIgnore
 */
abstract class Base extends \PHPUnit_Framework_TestCase
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
                        Argument::that(function ($value) {
                            return
                                ($value instanceof \BO\Zmsentities\Schema\Entity) ||
                                ($value instanceof \BO\Zmsentities\Collection\Base);
                        }),
                        $parameters
                    ]
                );
            }
            if (isset($options['exception'])) {
                $function->will(new \Prophecy\Promise\ThrowPromise($options['exception']));
            } elseif (isset($options['response'])) {
                $function->shouldBeCalled()
                    ->willReturn(
                        new \BO\Zmsclient\Result(
                            $this->getResponse($options['response'], 200),
                            $this->getRequest()
                        )
                    );
            }
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
