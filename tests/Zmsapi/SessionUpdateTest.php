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
        $this->assertContains('session.json', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->setExpectedException('BO\Mellon\Failure\Exception');
        $this->render([], [
            '__body' => '',
        ], []);
    }

    public function testUnknownRequest()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Matching\RequestNotFound');
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
        $this->setExpectedException('\BO\Zmsapi\Exception\Matching\ProviderNotFound');
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
        $this->setExpectedException('\BO\Zmsapi\Exception\Matching\MatchingNotFound');
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

    public function testInvalid()
    {
        $this->setExpectedException('\BO\Zmsapi\Exception\Session\InvalidSession');
        $this->render([], [
            '__body' => '{
                "id": "unittest",
                "name": "unittest",
                "status": "start",
                "content": {
                    "basket": {
                        "requests" : "120703",
                        "providers" : "122282"
                    }
                }
        }',
        ], []);
    }
}
