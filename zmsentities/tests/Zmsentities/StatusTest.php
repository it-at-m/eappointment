<?php

namespace BO\Zmsentities\Tests;

class StatusTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Status';

    public function testStatisticDefaults(): void
    {
        $entity = new \BO\Zmsentities\Status();

        $this->assertSame('unknown', $entity['statistics']['status']);
        $this->assertSame('', $entity['statistics']['checkedDate']);
        $this->assertSame('', $entity['statistics']['checkedAt']);
        $this->assertSame(0, $entity['statistics']['statisticsChecked']);
        $this->assertSame(0, $entity['statistics']['statisticsMissing']);
        $this->assertSame([], $entity['statistics']['missing']);
    }
}
