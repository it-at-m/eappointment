<?php

namespace BO\Zmsapi\Tests;

class SessionUpdateTest extends Base
{
    protected $classname = "SessionUpdate";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest"
            }',
        ], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testIsDifferent()
    {
        $response = $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "content": {
                    "basket": {
                        "providers" : "999999999"
                    },
                    "entry": {
                        "providers": "122222"
                    },
                    "error": "isDifferent"
                }
            }',
        ], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testUnvalidInput()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{}'
        ], []);
    }

    public function testUnknownRequest()
    {
        $this->expectException('\BO\Zmsapi\Exception\Matching\RequestNotFound');
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "content": {
                    "basket": {
                        "requests" : "999999999"
                    }
                }
        }',
        ], []);
    }

    public function testUnknownProvider()
    {
        $this->expectException('\BO\Zmsapi\Exception\Matching\ProviderNotFound');
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "content": {
                    "basket": {
                        "providers" : "999999999"
                    }
                }
        }',
        ], []);
    }

    public function testNotMatching()
    {
        $this->expectException('\BO\Zmsapi\Exception\Matching\MatchingNotFound');
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "content": {
                    "basket": {
                        "requests" : "120703",
                        "providers" : "122222"
                    }
                }
        }',
        ], []);
    }

    public function testMatchingRequestWithProviderFromScope()
    {
        $response = $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "content": {
                    "basket": {
                        "requests" : "120703",
                        "scope" : "141"
                    }
                }
        }',
        ], []);
        $this->assertStringContainsString('session.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
