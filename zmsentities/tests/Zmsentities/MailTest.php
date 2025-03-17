<?php

namespace BO\Zmsentities\Tests;

class MailTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Mail';

    public $collectionclass = '\BO\Zmsentities\Collection\MailList';

    const DEFAULT_TIME = '2016-04-01 11:55:00';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue(123456 == $entity->getProcessId(), 'Getting process id failed');
        $this->assertTrue('1234' == $entity->getProcessAuthKey(), 'Getting AuthKey failed');
        $this->assertTrue('Max Mustermann' == $entity->getClient()['familyName'], 'Getting client failed');
        $this->assertTrue($entity->hasContent(), 'Missing content in mail');
    }

    public function testDateformat()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $dateTime = new \DateTimeImmutable("2019-12-30 11:55:00", new \DateTimeZone('Europe/Berlin'));
        $process->getFirstAppointment()->setDateTime($dateTime);
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString(
            'Montag, 30. Dezember 2019',
            $resolvedEntity->getHtmlPart(),
            'Wrong date/time format'
        );
        $this->assertStringContainsString(
            '11:55 Uhr',
            $resolvedEntity->getHtmlPart(),
            'Wrong date/time format'
        );

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
        $this->assertFalse($entity->hasIcs(), 'Mimepart with mime text/calendar should not exist');
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString(
            'Guten Tag',
            $resolvedEntity->getHtmlPart(),
            'Mimepart content is not html'
        );
        $this->assertStringContainsString('BEGIN:VCALENDAR', $resolvedEntity->getIcsPart(), 'Mimepart content is not plain text');
        // test if appointment date formatted correct
        $this->assertStringContainsString('18. November 2015', $resolvedEntity->getIcsPart());
        $this->assertStringContainsString('18:52 Uhr', $resolvedEntity->getIcsPart());
        $this->assertStringContainsString('DTSTART;TZID=Europe/Berlin:20151118T185251', $resolvedEntity->getIcsPart());
    }

    public function testICS()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests[] = (new \BO\Zmsentities\Request())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');

        $this->assertStringContainsString('Achtung! Dies ist eine automatisch erstellte E-Mail', $resolvedEntity->getIcsPart(), 'ICS content is not valid');
        $this->assertStringContainsString('Guten Tag', $resolvedEntity->getIcsPart(), 'ICS content is not valid');
        // test if appointment date formatted correct
    }

    public function testConfirmationWithoutDataAttribute()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests->getFirst()->data = null;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString('Guten Tag', $resolvedEntity->getHtmlPart());
        $this->assertStringNotContainsString('Erforderliche Unterlagen', $resolvedEntity->getHtmlPart());
        $this->assertStringContainsString('BEGIN:VCALENDAR', $resolvedEntity->getIcsPart());
    }

    public function testMailWithInitiator()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'updated', 'admin');
        $this->assertStringContainsString(
            'Geändert wurde der Termin von Max Mustermann',
            $resolvedEntity->getHtmlPart(),
            'Mimepart content is not html'
        );
        $this->assertStringContainsString(
            'Die Terminänderung wurde initiiert via "admin"',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
    }

    public function testMailWithSurveyAccepted()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->scope['preferences']['survey']['emailContent'] = 'Das ist eine Umfrage';
        $process->getFirstClient()->surveyAccepted = 1;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'survey');
        $this->assertStringContainsString(
            'Das ist eine Umfrage',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
    }

    public function testQueuedMailWithOneRequest()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'queued');
        $this->assertStringContainsString('Sie haben folgende Dienstleistung ausgewählt:', $resolvedEntity->getPlainPart());
    }

    public function testQueuedMailWithMultipleRequests()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests[] = (new \BO\Zmsentities\Request())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'queued');
        $this->assertStringContainsString('Sie haben folgende Dienstleistungen ausgewählt:', $resolvedEntity->getPlainPart());
    }

    public function testQueuedMailWithoutRequests()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests = [];
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'queued');
        $this->assertStringContainsString('Sie haben keine Dienstleistungen ausgewählt.', $resolvedEntity->getPlainPart());
    }

    public function testMailWithOneRequest()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString('Folgende Dienstleistung ist für diesen Termin gebucht:', $resolvedEntity->getPlainPart());
    }

    public function testMailWithMultipleRequests()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests[] = (new \BO\Zmsentities\Request())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString('Folgende Dienstleistungen sind für diesen Termin gebucht:', $resolvedEntity->getPlainPart());
    }

    public function testMailWithoutRequests()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->requests = [];
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'appointment');
        $this->assertStringContainsString('Sie haben keine Dienstleistungen ausgewählt.', $resolvedEntity->getPlainPart());
    }

    public function testTemplateNotFound()
    {
        $this->expectException('\BO\Zmsentities\Exception\TemplateNotFound');
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->getFirstClient()->surveyAccepted = 0;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'finished');
    }

    public function testToCustomMessageEntity()
    {
        $entity = (new $this->entityclass());
        $process = (new \BO\Zmsentities\Process())->getExample();
        $formCollection = array(
            'subject' => \BO\Mellon\Validator::value('Das ist ein Test')->isString()->isBiggerThan(2),
            'message' => \BO\Mellon\Validator::value('Das ist eine Testnachricht')->isString()->isBiggerThan(2)
        );
        $formCollection = \BO\Mellon\Validator::collection($formCollection);
        $customEntity = $entity->toCustomMessageEntity($process, $formCollection->getValues());
        $this->assertEquals('Das ist ein Test', $customEntity->subject);
        $this->assertEquals('Das ist eine Testnachricht', $customEntity->getPlainPart());
        $this->assertFalse($customEntity->hasIcs());
    }

    public function testToCustomMessageEntityWithoutClient()
    {
        $entity = (new $this->entityclass());
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->addClientFromForm(['email' => 'unittest@berlinonline.de', 'familyName' => 'Unittest']);
        $formCollection = array(
            'subject' => \BO\Mellon\Validator::value('Das ist ein Test')->isString()->isBiggerThan(2),
            'message' => \BO\Mellon\Validator::value('Das ist eine Testnachricht')->isString()->isBiggerThan(2)
        );
        $formCollection = \BO\Mellon\Validator::collection($formCollection);
        $entity->client = null;
        $customEntity = $entity->toCustomMessageEntity($process, $formCollection->getValues());
        $this->assertEquals('unittest@berlinonline.de', $customEntity['client']['email']);
        $this->assertEquals('Das ist ein Test', $customEntity->subject);
        $this->assertEquals('Das ist eine Testnachricht', $customEntity->getPlainPart());
        $this->assertFalse($customEntity->hasIcs());
    }

    public function testToScopeAdminProcessList()
    {
        $now = new \DateTimeImmutable(self::DEFAULT_TIME);
        $process = (new \BO\Zmsentities\Process())->getExample();
        $processList = (new \BO\Zmsentities\Collection\ProcessList())->addEntity($process);
        $scope = (new \BO\Zmsentities\Scope())->getExample();
        $entity = (new $this->entityclass())->toScopeAdminProcessList($processList, $scope, $now);
        $this->assertEquals('Termine am 2016-04-01', $entity->subject);
        $this->assertStringContainsString('Termine am 2016-04-01 (1 gesamt)', $entity->getHtmlPart());
        $this->assertStringContainsString('18:52 <small>(2)</small>', $entity->getHtmlPart());
        $this->assertEquals(null, $entity->getPlainPart());
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

    public function testMailTemplatesAll()
    {
        $statusList = array(
            'queued',
            'appointment',
            'reminder',
            'pickup',
            'deleted',
            'blocked',
            'survey'
        );
        $statusAdminList = array(
            'deleted',
            'blocked',
            'updated'
        );
        $statusFailedList = array(
            "free",
            "reserved",
            "called",
            "processing",
            "pending",
            "finished",
            "missed",
            "archived",
            "anonymized",
            "conflict",
            ""
        );
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process->scope = (new \BO\Zmsentities\Scope())->getExample();
        $process->queue->withAppointment = false;

        //survey mail with and without clientname
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array());
        $entity->client = null;
        $resolvedEntity = $entity->toResolvedEntity($process, $config, 'survey');
        $this->assertStringContainsString('Sehr geehrte', $resolvedEntity->getPlainPart());
        $processSurvey = clone $process;
        $processSurvey->getFirstClient()->familyName = null;
        $resolvedEntity = $entity->toResolvedEntity($processSurvey, $config, 'survey');
        $this->assertStringContainsString('Guten Tag', $resolvedEntity->getPlainPart());
        
        //all others by status
        foreach ($statusList as $status) {
            $config = (new \BO\Zmsentities\Config())->getExample();
            $entity->addMultiPart(array());
            $entity->client = null;
            $resolvedEntity = $entity->toResolvedEntity($process, $config, $status);
            $resultString = $resolvedEntity->getPlainPart();

            if (in_array($status, array('deleted','blocked','appointment','reminder')) ) {
                $this->assertStringContainsString('Guten Tag', $resultString);
            } else {
                $this->assertStringContainsString('Sehr geehrte', $resultString);
            }

            $this->assertStringContainsString(
                'Achtung! Dies ist eine automatisch erstellte E-Mail.',
                $resolvedEntity->getPlainPart()
            );
        }

        foreach ($statusAdminList as $status) {
            $config = (new \BO\Zmsentities\Config())->getExample();
            $entity->addMultiPart(array());
            $entity->client = null;
            $resolvedEntity = $entity->toResolvedEntity($process, $config, $status, 'unittest');
            $this->assertStringContainsString('initiiert via "unittest"', $resolvedEntity->getPlainPart());
        }

        foreach ($statusFailedList as $status) {
            $this->expectException('BO\Zmsentities\Exception\TemplateNotFound');
            $config = (new \BO\Zmsentities\Config())->getExample();
            $entity->addMultiPart(array());
            $entity->client = null;
            $resolvedEntity = $entity->toResolvedEntity($process, $config, $status);
        }
    }
}
