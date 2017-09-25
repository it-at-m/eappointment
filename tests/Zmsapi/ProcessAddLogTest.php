<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessAddLogTest extends Base
{
    protected $classname = "ProcessAddLog";

    const PROCESS_ID = 10030;

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => self::PROCESS_ID], [
            '__body' => $this->readFixture('GetMail.json')
        ], []);
        $this->assertContains('MTA successful, subject=Example Mail', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render(['id' => self::PROCESS_ID], [
            '__body' => '{
              "createIP": "145.15.3.10",
              "createTimestamp": 1447931596000,
              "multipart": [
                {
                  "queueId": "1234",
                  "mime": "text/html",
                  "content": "<h1>Title</h1><p>Message</p>",
                  "base64": false
                },
                {
                  "queueId": "1234",
                  "mime": "text/plain",
                  "content": "Title\nMessage",
                  "base64": false
                }
              ],
              "subject": "Example Mail"
            }'
        ], []);
    }

    public function testMissingRights()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->expectExceptionCode(403);
        $this->render(['id' => self::PROCESS_ID], [
            '__body' => '{}'
        ], []);
    }

    public function testProcessIdUnvalid()
    {
        $this->setWorkstation();
        $this->expectException('\Exception');
        $this->expectExceptionCode(403);
        $this->render(['id' => 'xvz'], [
            '__body' => '{}'
        ], []);
    }
}
