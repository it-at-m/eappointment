<?php

namespace BO\Zmsentities\Tests;

class LinkTest extends EntityCommonTests
{
    const DEFAULT_TIME = '2015-11-18 11:55:00';

    public $entityclass = '\BO\Zmsentities\Link';

    public $collectionclass = '\BO\Zmsentities\Collection\LinkList';

    public function testBasic()
    {
        $entity = $this->getExample();
        $this->assertStringContainsString('Link Zuständigkeitsverzeichnis', $entity->__toString(), 'link to string failed');
    }
}
