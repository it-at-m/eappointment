<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessLogTest extends Base
{
    protected $classname = "ProcessLog";

    const SEARCH_PARAM = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        (new ProcessUpdateTest('dummyTest'))->testRendering();
        $useraccount = $this->setWorkstation()->getUseraccount();
        $useraccount->setRights('superuser');
        $useraccount->permissions['logs'] = true;
        $useraccount->permissions['customersearch'] = true;
        $response = $this->render([], ['searchQuery' => self::SEARCH_PARAM], []);
        $this->assertStringContainsString('log.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $useraccount = $this->setWorkstation()->getUseraccount();
        $useraccount->setRights('superuser');
        $useraccount->permissions['logs'] = true;
        $useraccount->permissions['customersearch'] = true;
        $response = $this->render([], ['searchQuery' => 123456], []);
        $this->assertStringContainsString('"data":[]', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testAuditViewerHasAccess()
    {
        $useraccount = $this->setWorkstation()->getUseraccount();
        $useraccount->permissions['logs'] = true;
        $useraccount->permissions['customersearch'] = true;
        $response = $this->render([], ['searchQuery' => self::SEARCH_PARAM], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReportingViewerHasNoLogsAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingPermissions');
        $useraccount = $this->setWorkstation()->getUseraccount();
        $useraccount->permissions['statistic'] = true;
        $response = $this->render([], ['searchQuery' => self::SEARCH_PARAM], []);
    }
}
