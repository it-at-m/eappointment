<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Office\Service;

use BO\Zmscitizenbackend\Office\Model\Collections\OfficeList;
use BO\Zmscitizenbackend\Office\Model\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Office\Model\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Office\Model\Collections\ServiceList;
use BO\Zmscitizenbackend\Office\Repository\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Office\Service\OfficesServicesRelationsService;
use PHPUnit\Framework\TestCase;

class OfficesServicesRelationsServiceTest extends TestCase
{
    private OfficesServicesRelationsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfficesServicesRelationsService();
        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    protected function tearDown(): void
    {
        OfficesServicesRelationsRepository::use(null);
        if (\App::$cache) {
            \App::$cache->clear();
        }
        parent::tearDown();
    }

    public function testGetServicesAndOfficesListReturnsOfficeServiceAndRelationList(): void
    {
        $expectedList = new OfficeServiceAndRelationList(new OfficeList(), new ServiceList(), new OfficeServiceRelationList());
        $this->stubRepository($expectedList);

        $result = $this->service->getServicesAndOfficesList();

        $this->assertInstanceOf(OfficeServiceAndRelationList::class, $result);
        $this->assertSame($expectedList, $result);

        $resultArray = $result->toArray();
        $this->assertArrayHasKey('offices', $resultArray);
        $this->assertArrayHasKey('services', $resultArray);
        $this->assertArrayHasKey('relations', $resultArray);
    }

    public function testGetServicesAndOfficesListReturnsEmptyList(): void
    {
        $expectedList = new OfficeServiceAndRelationList(new OfficeList(), new ServiceList(), new OfficeServiceRelationList());
        $this->stubRepository($expectedList);

        $result = $this->service->getServicesAndOfficesList();

        $this->assertInstanceOf(OfficeServiceAndRelationList::class, $result);

        $resultArray = $result->toArray();
        $this->assertEmpty($resultArray['offices']);
        $this->assertEmpty($resultArray['services']);
        $this->assertEmpty($resultArray['relations']);
    }

    public function testGetServicesAndOfficesListPassesShowUnpublished(): void
    {
        $expectedList = new OfficeServiceAndRelationList(new OfficeList(), new ServiceList(), new OfficeServiceRelationList());
        $repository = $this->createMock(OfficesServicesRelationsRepository::class);
        $repository->expects($this->once())
            ->method('readOfficesAndServices')
            ->with(true)
            ->willReturn($expectedList);
        OfficesServicesRelationsRepository::use($repository);

        $result = $this->service->getServicesAndOfficesList(true);

        $this->assertSame($expectedList, $result);
    }

    private function stubRepository(OfficeServiceAndRelationList $returnValue): void
    {
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readOfficesAndServices')->willReturn($returnValue);
        OfficesServicesRelationsRepository::use($repository);
    }
}
