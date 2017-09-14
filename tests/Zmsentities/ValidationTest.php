<?php

namespace BO\Zmsentities\Tests;

class ValidationTest extends Base
{
    public function testTestValid()
    {
        $entity = new \BO\Zmsentities\Useraccount();
        $entity->id= "123";
        $entity->changePassword= array('test', 'testfailed');
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testTestValidObject()
    {
        $entity = (new \BO\Zmsentities\Scope())->getExample();
        $entity->preferences['client']['emailFrom'] = "test.de";
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $this->assertContains(
                'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
                $exception->getMessage()
            );
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testLocale()
    {
        $entity = new \BO\Zmsentities\Useraccount();
        $entity->id= "1234";
        $entity->changePassword= array('test', 'testfailed');
        try {
            $entity->testValid();
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            $errorList = $exception->getValidationErrorList();
            $this->assertEquals('Passwortwiederholung', $errorList[0]->getPointer());
            $this->assertEquals('Passwortwiederholung', $errorList[1]->getPointer());
            $this->assertContains('Zeichen', $errorList[0]->getMessage());
            $this->assertContains('identisch', $errorList[1]->getMessage());
        }
    }
}
