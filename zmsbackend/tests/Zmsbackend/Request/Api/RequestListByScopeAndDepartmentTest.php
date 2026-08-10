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
        $this->assertArrayHasKey('scopeRequests', $body['data']);
        $this->assertArrayHasKey('additionalDepartmentRequests', $body['data']);
        $this->assertIsArray($body['data']['scopeRequests']);
        $this->assertIsArray($body['data']['additionalDepartmentRequests']);
        $this->assertNotEmpty($body['data']['scopeRequests']);
        $this->assertStringContainsString('requeststatistic.json', (string) $response->getBody());
        $this->assertStringContainsString('request.json', (string) $response->getBody());
    }

    public function testAdditionalDepartmentRequestsDoNotDuplicateScopeRequests()
    {
        $response = $this->render(['id' => 141], [], []);
        $body = json_decode((string) $response->getBody(), true);

        $scopeKeys = array_map(static function ($request) {
            return ($request['source'] ?? '') . ':' . $request['id'];
        }, $body['data']['scopeRequests']);
        $additionalDepartmentKeys = array_map(static function ($request) {
            return ($request['source'] ?? '') . ':' . $request['id'];
        }, $body['data']['additionalDepartmentRequests']);

        $this->assertSame([], array_values(array_intersect($scopeKeys, $additionalDepartmentKeys)));
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
