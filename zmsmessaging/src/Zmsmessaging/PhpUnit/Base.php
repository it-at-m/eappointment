<?php

/**
 *
 */

namespace BO\Zmsmessaging\PhpUnit;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;

/**
 * @codeCoverageIgnore
 */
abstract class Base extends TestCase
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
    use ProphecyTrait;

    protected $apiCalls = array();

    public function setUp(): void
    {
        \App::$http = $this->getApiMockup();
    }

    public function tearDown(): void
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
            } else {
                $function = $mock->__call(
                    $function,
                    $parameters
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
            } else {
                $function->shouldBeCalled();
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
