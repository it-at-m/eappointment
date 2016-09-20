<?php

namespace BO\Zmsentities\Tests;

class ProviderTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Provider';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertFalse($entity->hasRequest('1234'), 'Request should not be existing');
        $entity['data']['services'] = array('service' => '1234');
        $this->assertTrue($entity->hasRequest('1234'), 'Request should be existing');
    }
}
