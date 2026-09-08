<?php

namespace BO\Zmsentities\Tests;

class UseraccountprofileTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Useraccountprofile';

    public function testBasic(): void
    {
        $entity = (new $this->entityclass())->getExample();

        $this->assertEntity($this->entityclass, $entity);
        $this->assertSame(
            'max.mustermann',
            $entity->username
        );
        $this->assertSame(
            'Sachbearbeitung (Basis)',
            $entity->role
        );
        $this->assertNotEmpty($entity->permissions);
    }

    public function testGetDefaults(): void
    {
        $entity = new $this->entityclass();

        $this->assertSame(
            [],
            $entity->getDefaults()['permissions']
        );
    }
}
