<?php

namespace BO\Zmsentities\Tests;

class MimepartTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Mimepart';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertEquals('html', $entity->getExtension(), 'Getting image type from mime failed');
        $this->assertEquals('<h1>Title</h1><p>Message</p>', $entity->getContent(), 'Getting content failed');
    }
}
