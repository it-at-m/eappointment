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

    public function testWithGraphQL()
    {
        $response = $this->render(
            ['id' => 10030, 'authKey' => '1c56'],
            ['gql' => '{ id authKey scope{ id source shortName } }'],
            []
        );
        $this->assertStringContainsString('"id":"141","source":"dldb","shortName":""', (string)$response->getBody());
        $this->assertStringNotContainsString('"provider":{"id":"122208","source":"dldb"}', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithGraphQLInvalid()
    {
        $this->expectException('\BO\Zmsapi\Response\GraphQLException');
        $this->expectExceptionMessage('No valid graphql');
        $this->setWorkstation();
        $this->render(['id' => 10030, 'authKey' => '1c56'], ['gql' => 'test'], []);
    }

    public function testWithGraphQLEmptyContent()
    {
        $this->expectException('\BO\Zmsapi\Response\GraphQLException');
        $this->expectExceptionMessage('No content for graph');
        $this->setWorkstation();
        $this->render(['id' => 10030, 'authKey' => '1c56'], ['gql' => '[]'], []);
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
