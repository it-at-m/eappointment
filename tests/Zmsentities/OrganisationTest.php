<?php

namespace BO\Zmsentities\Tests;

class OrganisationTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Organisation';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasDepartment(123), 'department with id 123 not found');
        $this->assertFalse($entity->getPreference('ticketPrinterProtectionEnabled'), 'get preference failed');
    }
}
