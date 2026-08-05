<?php

namespace BO\Zmsbackend\Tests\Request\Api;

class RequestListByScopeAndDepartmentTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "RequestListByScopeAndDepartment";

    public function testRendering()
    {
        $response = $this->render(['id' => 141], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body['data']);
        $this->assertArrayHasKey('scope', $body['data']);
        $this->assertArrayHasKey('additional', $body['data']);
        $this->assertIsArray($body['data']['scope']);
        $this->assertIsArray($body['data']['additional']);
        $this->assertNotEmpty($body['data']['scope']);
        $this->assertStringContainsString('requeststatistic.json', (string) $response->getBody());
        $this->assertStringContainsString('request.json', (string) $response->getBody());
    }

    public function testAdditionalDoesNotDuplicateScopeRequests()
    {
        $response = $this->render(['id' => 141], [], []);
        $body = json_decode((string) $response->getBody(), true);

        $scopeKeys = array_map(static function ($request) {
            return ($request['source'] ?? '') . ':' . $request['id'];
        }, $body['data']['scope']);
        $additionalKeys = array_map(static function ($request) {
            return ($request['source'] ?? '') . ':' . $request['id'];
        }, $body['data']['additional']);

        $this->assertSame([], array_values(array_intersect($scopeKeys, $additionalKeys)));
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 9999], [], []);
    }
}
