<?php

namespace BO\Zmsbackend\Tests\Useraccount\Api;

class UserQueueGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = 'UserQueue';

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('openqueue');
        $this->setDepartment(74);

        $response = $this->render([], [
            'status' => 'called,processing'
        ], []);

        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testAccessWithOpenqueuePermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('openqueue');

        $this->setDepartment(74);

        $response = $this->render([], [], []);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAccessWithoutOpenqueueButWithWaitingqueuePermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('waitingqueue');

        $this->setDepartment(74);

        $response = $this->render([], [], []);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAccessWithParkedqueuePermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('parkedqueue');

        $this->setDepartment(74);

        $response = $this->render([], [], []);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAccessWithMissedqueuePermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('missedqueue');

        $this->setDepartment(74);

        $response = $this->render([], [], []);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAccessWithFinishedqueuePermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('finishedqueue');

        $this->setDepartment(74);

        $response = $this->render([], [], []);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testAccessDeniedWithoutQueuePermission(): void
    {
        $this->setWorkstation();
        $this->setDepartment(74);

        $this->expectException(
            \BO\Zmsentities\Exception\UserAccountMissingRights::class
        );
        $this->expectExceptionCode(403);

        $this->render([], [], []);
    }

    public function testAccessDeniedWithUnrelatedPermission(): void
    {
        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('appointment');

        $this->setDepartment(74);

        $this->expectException(
            \BO\Zmsentities\Exception\UserAccountMissingRights::class
        );
        $this->expectExceptionCode(403);

        $this->render([], [], []);
    }
}
