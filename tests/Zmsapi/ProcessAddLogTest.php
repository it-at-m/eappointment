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
            '__body' => $this->readFixture('GetMimepart.json')
        ], []);
        $this->assertStringContainsString("mimepart.json", (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testHasError()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => self::PROCESS_ID], [
            '__body' => $this->readFixture('GetMimepart.json'),
            'error' => 1
        ], []);
        $this->assertStringContainsString("mimepart.json", (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
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
