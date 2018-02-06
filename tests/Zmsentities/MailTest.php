<?php

namespace BO\Zmsentities\Tests;

class MailTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Mail';

    public $collectionclass = '\BO\Zmsentities\Collection\MailList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(123456 == $entity->getProcessId(), 'Getting process id failed');
        $this->assertTrue('1234' == $entity->getProcessAuthKey(), 'Getting AuthKey failed');
        $this->assertTrue('Max Mustermann' == $entity->getFirstClient()['familyName'], 'Getting first client failed');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = (new $this->entityclass())->getExample();
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue($collection->hasEntity(1234), "Missing Test Entity with ID 1234 in collection");
        $filterProcess = $collection->withProcess('123456');
        $this->assertCount(1, $filterProcess);
    }

    public function testMultiPart()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests[] = (new \BO\Zmsentities\Request())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $this->assertTrue(null === $entity->getHtmlPart(), 'Mimepart with mime text/html should not exist');
        $this->assertTrue(null === $entity->getPlainPart(), 'Mimepart with mime text/plain should not exist');
        $this->assertTrue(null === $entity->getIcsPart(), 'Mimepart with mime text/calendar should not exist');
        $resolvedEntity = $entity->toResolvedEntity($process, $config);
        $this->assertContains(
            'Sehr geehrte/r Frau',
            $resolvedEntity->getHtmlPart(),
            'Mimepart content is not html'
        );
        $this->assertContains(
            'Ihre Vorgangsnummer ist die **"123456"**',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
        $this->assertContains('BEGIN:VCALENDAR', $resolvedEntity->getIcsPart(), 'Mimepart content is not plain text');
    }

    public function testMailWithInitiator()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->status = 'updated';
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'admin');
        $this->assertContains(
            'Geändert wurde der Termin von Max Mustermann',
            $resolvedEntity->getHtmlPart(),
            'Mimepart content is not html'
        );
        $this->assertContains(
            'Die Terminänderung wurde initiiert via "admin"',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
    }

    public function testMailWithSurveyAccepted()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->status = 'finished';
        $process->scope['preferences']['survey']['emailContent'] = 'Das ist eine Umfrage';
        $process->getFirstClient()->surveyAccepted = 1;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config);
        $this->assertContains(
            'Das ist eine Umfrage',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
    }

    public function testToCustomMessageEntity()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $formCollection = array(
            'subject' => \BO\Mellon\Validator::value('Das ist ein Test')->isString()->isBiggerThan(2),
            'message' => \BO\Mellon\Validator::value('Das ist eine Testnachricht')->isString()->isBiggerThan(2)
        );
        $formCollection = \BO\Mellon\Validator::collection($formCollection);
        $entity = $entity->toCustomMessageEntity($process, $formCollection->getValues());
        $this->assertEquals('Das ist ein Test', $entity->subject);
        $this->assertEquals('Das ist eine Testnachricht', $entity->getPlainPart());
        $this->assertEquals(null, $entity->getIcsPart());
    }

    public function testWithDepartment()
    {
        $entity = (new $this->entityclass())->getExample();
        $department = (new \BO\Zmsentities\Department)->getExample();
        $entity->withDepartment($department);
        $this->assertEquals('Flughafen Schönefeld, Landebahn', $entity->department->getContact()->name);
    }

    public function testGetRecipient()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals('max@service.berlin.de', $entity->getRecipient());
    }

    public function testGetRecipientFailed()
    {
        $this->expectException('BO\Zmsentities\Exception\MailMissedAddress');
        $entity = (new $this->entityclass())->getExample();
        unset($entity->process);
        unset($entity->client);
        $entity->getRecipient();
    }
}
