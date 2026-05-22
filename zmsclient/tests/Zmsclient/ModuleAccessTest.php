<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use BO\Zmsclient\ModuleAccess;
use BO\Zmsentities\Useraccount;
use PHPUnit\Framework\TestCase;

class ModuleAccessTest extends TestCase
{
    public function testStatisticModuleRequiresStatisticPermission(): void
    {
        $without = new Useraccount(['permissions' => ['appointment' => true]]);
        $with = new Useraccount(['permissions' => ['statistic' => true]]);

        $this->assertTrue($this->isRejected(ModuleAccess::MODULE_STATISTIC, $without));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_STATISTIC, $with));
    }

    public function testAdminModuleRejectsStatisticOnlyUser(): void
    {
        $statisticOnly = new Useraccount(['permissions' => ['statistic' => true]]);
        $withAdmin = new Useraccount(['permissions' => ['statistic' => true, 'appointment' => true]]);
        $adminOnly = new Useraccount(['permissions' => ['appointment' => true]]);

        $this->assertTrue($this->isRejected(ModuleAccess::MODULE_ADMIN, $statisticOnly));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_ADMIN, $withAdmin));
        $this->assertFalse($this->isRejected(ModuleAccess::MODULE_ADMIN, $adminOnly));
    }

    private function isRejected(string $application, Useraccount $useraccount): bool
    {
        if ($useraccount->isSuperUser()) {
            return false;
        }

        return ($application === ModuleAccess::MODULE_STATISTIC && !$useraccount->hasPermissions(['statistic']))
            || ($application === ModuleAccess::MODULE_ADMIN && $useraccount->hasExclusivePermission('statistic'));
    }
}
