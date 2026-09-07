<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsclient\PhpUnit;

use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use BO\Zmsclient\GraphQL\GraphQLInterpreter;
use BO\Zmsclient\Http;
use BO\Zmsentities\Session;
use BO\Zmsclient\SessionHandler;

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

    protected array $apiCalls = array();

    public function setUp(): void
    {
        \App::$http = $this->getApiMockup();
        $this->sessionClass = new Session();
        $handler = SessionHandler::getLastInstance();
        if ($handler instanceof SessionHandler) {
            $handler->setHttpHandler(\App::$http);
        }
    }

    public function tearDown(): void
    {
    }

    /**
     * @SuppressWarnings(Cyclomatic)
     * @psalm-api
     */
    protected function getApiMockup(): Http
    {
        $mock = $this->prophesize(Http::class);
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
                        Argument::that(function (mixed $value): bool {
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
                    $encodedData = json_encode($responseData['data']);
                    $responseData['data'] = $graphqlInterpreter->setJson(
                        $encodedData === false ? 'null' : $encodedData
                    );
                }
                $encoded = json_encode($responseData);
                $function->shouldBeCalled()
                    ->willReturn(
                        new \BO\Zmsclient\Result(
                            $this->getResponse($encoded === false ? '{}' : $encoded, 200),
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
     * @psalm-api
     */
    protected function getApiCalls(): array
    {
        return $this->apiCalls;
    }

    /**
     * @psalm-api
     */
    protected function getGraphQL(?array $parameters): GraphQLInterpreter|null
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

    public function setApiCalls(array $apiCalls): void
    {
        $this->apiCalls = $apiCalls;
        \App::$http = $this->getApiMockup();
    }
}
