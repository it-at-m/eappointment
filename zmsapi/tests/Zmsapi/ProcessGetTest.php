<?php

namespace BO\Zmsapi\Tests;

class ProcessGetTest extends Base
{
    protected $classname = "ProcessGet";

    public function testRendering()
    {
        $response = $this->render(['id' => 10030, 'authKey' => '1c56'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithCompressLevel()
    {
        $response = $this->render(['id' => 10030, 'authKey' => '1c56'], ['compress' => 10], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGraphQL()
    {
        $gqlString = '{ id authKey scope{ id source shortName } appointments{date} }';
        $response = $this->render(
            ['id' => 10030, 'authKey' => '1c56'],
            ['gql' => $gqlString],
            []
        );

        $graphqlInterpreter = (new \BO\Zmsclient\GraphQL\GraphQLInterpreter($gqlString))
            ->setJson($this->readFixture('GetProcess_10030.json'));
        $this->assertStringContainsString('"appointments":[{"date":1463379000}]', (string)$graphqlInterpreter);

        $this->assertStringContainsString('"id":"141","source":"dldb","shortName":""', (string)$response->getBody());
        $this->assertStringNotContainsString('"provider":{"id":"122208","source":"dldb"}', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGraphQLInvalid()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('No valid graphql');
        $this->setWorkstation();
        $this->render(['id' => 10030, 'authKey' => '1c56'], ['gql' => 'test'], []);
    }

    public function testWithGraphQLEmptyContent()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('No content for graph');
        $this->setWorkstation();
        $this->render(['id' => 10030, 'authKey' => '1c56'], ['gql' => '[]'], []);
    }

    public function testWithGraphQLClosingBrackets()
    {
        $this->expectException('\BO\Zmsclient\GraphQL\GraphQLException');
        $this->expectExceptionMessage('Curly bracket match problem, too many closing brackets');
        $gqlString = '{ id authKey } }';
        $graphqlInterpreter = (new \BO\Zmsclient\GraphQL\GraphQLInterpreter($gqlString))
            ->setJson($this->readFixture('GetProcess_10030.json'));
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999999, 'authKey' => null], [], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->expectExceptionCode(403);
        $this->render(['id' => 10030, 'authKey' => null], [], []);
    }
}
