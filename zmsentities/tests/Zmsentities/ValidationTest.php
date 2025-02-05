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
            foreach ($exception->data as $error) {
                $error = $exception->data['/preferences/client/emailFrom'];
                $this->assertContainsEquals(
                    'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
                    $error['messages']
                );
            }
            $this->assertEquals(400, $exception->getCode());
        }
    }

    public function testTestValidObjectReference()
    {
        $entity = (new \BO\Zmsentities\Scope())->getExample();
        $entity['contact']['email'] = "test.de";
        try {
            $entity->testValid('de_DE', 1);
            $this->fail("Expected exception SchemaValidation not thrown");
        } catch (\BO\Zmsentities\Exception\SchemaValidation $exception) {
            foreach ($exception->data as $error) {
                $error = $exception->data['/contact/email'];
                $this->assertContainsEquals(
                    'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
                    $error['messages']
                );
            }
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
            $errorList = $exception->data;
            var_dump($errorList);
            $this->assertArrayHasKey('/changePassword', $errorList);
            $this->assertArrayHasKey('minLength', $errorList['/changePassword/0']['messages']);
            $this->assertArrayHasKey('format', $errorList['/changePassword/0']['messages']);
        }
    }
}
