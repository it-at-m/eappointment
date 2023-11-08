<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsclient\PhpUnit;

use \Prophecy\PhpUnit\ProphecyTrait;

use \Prophecy\Argument;

use \BO\Zmsclient\GraphQL\GraphQLInterpreter;

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

    use ProphecyTrait;
    
    protected $apiCalls = array();

    public function setUp(): void
    {
        \App::$http = $this->getApiMockup();
        $this->sessionClass = new \BO\Zmsentities\Session();
        if (\BO\Zmsclient\SessionHandler::getLastInstance() instanceof \BO\Zmsclient\SessionHandler) {
            \BO\Zmsclient\SessionHandler::getLastInstance()->setHttpHandler(\App::$http);
        }
    }

    public function tearDown(): void
    {
    }

    /**
     * @SuppressWarnings(Cyclomatic)
     * @return String
     */
    protected function getApiMockup()
    {
        $mock = $this->prophesize('BO\Zmsclient\Http');
        foreach ($this->getApiCalls() as $options) {
            $parameters = isset($options['parameters']) ? $options['parameters'] : null;
            $xtoken = isset($options['xtoken']) ? $options['xtoken'] : null;
            $function = $options['function'];
            if ($function == 'readGetResult' || $function == 'readDeleteResult') {
                $function = $mock->__call(
                    $function,
                    [
                        $options['url'],
                        $parameters,
                        $xtoken
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
                    [
                        $parameters
                    ]
                );
            }
            if (isset($options['exception'])) {
                $function->will(new \Prophecy\Promise\ThrowPromise($options['exception']));
            } elseif (isset($options['response'])) {
                $responseData = json_decode($options['response'], true);
                $graphqlInterpreter = $this->getGraphQL($parameters);
                if ($graphqlInterpreter) {
                    $responseData['data'] = $graphqlInterpreter->setJson(json_encode($responseData['data']));
                }
                $function->shouldBeCalled()
                    ->willReturn(
                        new \BO\Zmsclient\Result(
                            $this->getResponse(json_encode($responseData), 200),
                            static::createBasicRequest()
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

    protected function getGraphQL($parameters)
    {
        if (isset($parameters['gql'])) {
            $gqlString = $parameters['gql'];
            if ($gqlString) {
                $graphqlInterpreter = new GraphQLInterpreter($gqlString);
                return $graphqlInterpreter;
            }
        }
        return null;
    }

    public function setApiCalls($apiCalls)
    {
        $this->apiCalls = $apiCalls;
        \App::$http = $this->getApiMockup();
    }
}
