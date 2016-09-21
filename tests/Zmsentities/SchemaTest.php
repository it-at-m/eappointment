<?php

namespace BO\Zmsentities\Tests;

class SchemaTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Department';

    public function testEntity()
    {
        $entity = new $this->entityclass();
        $data = $this->getTestEntityMapping();
        $entity->exchangeArray($data);

        $this->assertTrue('Berlin' == $entity->contact['city'], 'Schema Helper exchangeArray  failed');
        $this->assertTrue($entity->testValid(), 'Schema Helper testValid failed');
        $this->assertContains('$schema', $entity->__toString(), 'Schema Helper __toString failed ($schema not found)');
    }

    public function testCreateExample()
    {
        $entity = new $this->entityclass();
        $entity = $entity->createExample();
        $this->assertTrue('Germany' == $entity->contact['country'], 'Schema Helper createExample failed');
        //no example
        $entity = new \BO\Zmsentities\Ics();
        $entity = $entity->createExample();
        $this->assertTrue(0 == count($entity), 'Schema Helper getExample from entity without example failed');
    }

    public function testTestValid()
    {
        $entity = new $this->entityclass();
        $entity->id = 'Ident';
        try {
            $entity->testValid();
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $this->assertEquals(500, $exception->getCode());
        }
    }

    public function testJsonSerialize()
    {
        $entity = new $this->entityclass();
        $jsonSerialize = json_decode(json_encode($entity));
        $this->assertTrue($jsonSerialize == $entity->jsonSerialize(), "Schema Helper jsonSerialize does not match");
    }

    protected function getTestEntityMapping()
    {
        return [
            'contact__city' => 'Berlin',
            'contact__street' => 'Zaunstraße',
            'contact__country' => 'Germany',
            'contact__name' => 'Flughafen Schönefeld, Landebahn',
            'email' => 'terminvereinbarung@mitte.berlin.de',
            'id' => '1234',
            'name' => 'Flughafen Schönefeld, Landebahn',
            'preferences__notifications__enabled' => true,
            'preferences__notifications__identification' => 'terminvereinbarung@mitte.berlin.de',
            'preferences__notifications__sendConfirmationEnabled' => true,
            'preferences__notifications__sendReminderEnabled' => true
        ];
    }
}
