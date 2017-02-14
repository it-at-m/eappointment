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
    }

    public function testMultiPart()
    {
        $entity = (new $this->entityclass())->getExample();
        $process = (new \BO\Zmsentities\Process())->getExample();
        $config = (new \BO\Zmsentities\Config())->getExample();
        $entity->addMultiPart(array ());
        $this->assertTrue(null === $entity->getHtmlPart(), 'Mimepart with mime text/html should not exist');
        $this->assertTrue(null === $entity->getPlainPart(), 'Mimepart with mime text/plain should not exist');
        $this->assertTrue(null === $entity->getIcsPart(), 'Mimepart with mime text/calendar should not exist');
        $resolvedEntity = $entity->toResolvedEntity($process, $config);
        $this->assertContains(
            '<strong>Sehr geehrte/r Frau',
            $resolvedEntity->getHtmlPart(),
            'Mimepart content is not html'
        );
        $this->assertContains(
            'Ihre Vorgangsnummer ist die "123456"',
            $resolvedEntity->getPlainPart(),
            'Mimepart content is not plain text'
        );
        $this->assertContains('BEGIN:VCALENDAR', $resolvedEntity->getIcsPart(), 'Mimepart content is not plain text');
    }
}
