<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\ModuleAccess;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use PHPUnit\Framework\TestCase;

class ModuleAccessTest extends TestCase
{
    public function testStatisticModuleRequiresStatisticPermission(): void
    {
        $without = $this->workstation(['appointment' => true]);
        $with = $this->workstation(['statistic' => true]);

        $this->assertTrue($this->isRejected(ModuleAccess::MODULE_STATISTIC, $without));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_STATISTIC, $with));
    }

    public function testAdminModuleRejectsStatisticOnlyUser(): void
    {
        $statisticOnly = $this->workstation(['statistic' => true]);
        $withAdmin = $this->workstation(['statistic' => true, 'appointment' => true]);
        $adminOnly = $this->workstation(['appointment' => true]);

        $this->assertTrue($this->isRejected(ModuleAccess::MODULE_ADMIN, $statisticOnly));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_ADMIN, $withAdmin));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_ADMIN, $adminOnly));
    }

    private function workstation(array $permissions): Workstation
    {
        return new Workstation([
            'id' => 1,
            'authkey' => 'test-authkey',
            'useraccount' => new Useraccount(['permissions' => $permissions]),
        ]);
    }

    private function isRejected(string $application, Workstation $workstation): bool
    {
        $useraccount = $workstation->getUseraccount();

        if ($useraccount->isSuperUser()) {
            return false;
        }

        return ($application === ModuleAccess::MODULE_STATISTIC && !$useraccount->hasPermissions(['statistic']))
            || ($application === ModuleAccess::MODULE_ADMIN && $useraccount->hasExclusivePermission('statistic'));
    }
}
