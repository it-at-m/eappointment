<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository\Office;

use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Models\Collections\ServiceList;
use BO\Zmscitizenbackend\Repository\Office\OfficesServicesRelationsRepository;
use PHPUnit\Framework\TestCase;

class OfficesServicesRelationsRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        OfficesServicesRelationsRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(OfficesServicesRelationsRepository::class);
        OfficesServicesRelationsRepository::use($override);
        $this->assertSame($override, OfficesServicesRelationsRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $empty = new OfficeServiceAndRelationList(new OfficeList(), new ServiceList(), new OfficeServiceRelationList());
        $override = $this->createStub(OfficesServicesRelationsRepository::class);
        $override->method('readOfficesAndServices')->willReturn($empty);
        $override->method('readServiceIdsByOfficeId')->willReturn([]);
        $override->method('isCaptchaRequiredForOfficeIds')->willReturn(false);
        OfficesServicesRelationsRepository::use($override);
        OfficesServicesRelationsRepository::use(null);

        $created = OfficesServicesRelationsRepository::create();
        $this->assertInstanceOf(OfficesServicesRelationsRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
