<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Office\Repository;

use BO\Zmscitizenbackend\Office\Model\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Office\Repository\OfficesServicesRelationsHydrator;
use BO\Zmscitizenbackend\Office\Repository\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Tests\Office\Helper\OfficesServicesRelationsRows;
use PHPUnit\Framework\TestCase;

class OfficesServicesRelationsHydratorTest extends TestCase
{
    protected function tearDown(): void
    {
        OfficesServicesRelationsRepository::use(null);
        parent::tearDown();
    }

    public function testHydrateHidesUnpublishedServices(): void
    {
        $list = (new OfficesServicesRelationsHydrator())->hydrate(
            OfficesServicesRelationsRows::officeRows(),
            OfficesServicesRelationsRows::requestRows(),
            OfficesServicesRelationsRows::relationRows(),
            false
        );

        $this->assertInstanceOf(OfficeServiceAndRelationList::class, $list);
        $payload = $list->toArray();
        $this->assertCount(2, $payload['offices']);
        $this->assertSame([1], array_column($payload['services'], 'id'));
        $this->assertCount(3, $payload['relations']);
        $this->assertSame('Unittest', $payload['offices'][0]['name']);
        $this->assertSame(1, $payload['offices'][0]['scope']['id']);
        $this->assertFalse($payload['offices'][0]['scope']['emailRequired']);
        $this->assertSame(9999998, $payload['relations'][0]['officeId']);
    }

    public function testHydrateIncludesUnpublishedServicesWhenRequested(): void
    {
        $list = (new OfficesServicesRelationsHydrator())->hydrate(
            OfficesServicesRelationsRows::officeRows(),
            OfficesServicesRelationsRows::requestRows(),
            OfficesServicesRelationsRows::relationRows(),
            true
        );

        $payload = $list->toArray();
        $this->assertSame([1, 2], array_column($payload['services'], 'id'));
        $combinable = json_decode(json_encode($payload['services'][1]['combinable']), true);
        $this->assertSame([9999999], $combinable['1']['1']);
        $this->assertSame([9999999], $combinable['2']['2']);
    }

    public function testHydrateWalksRootParentId(): void
    {
        $requests = [
            [
                'id' => '10',
                'source' => 'unittest',
                'name' => 'Child',
                'parent_id' => '20',
                'variant_id' => null,
                'data' => [],
            ],
            [
                'id' => '20',
                'source' => 'unittest',
                'name' => 'Parent',
                'parent_id' => null,
                'variant_id' => null,
                'data' => [],
            ],
        ];
        $relations = [
            [
                'provider__id' => '1',
                'request__id' => '10',
                'source' => 'unittest',
                'slots' => 1,
                'public_visibility' => 1,
                'max_quantity' => null,
            ],
        ];

        $list = (new OfficesServicesRelationsHydrator())->hydrate([], $requests, $relations, false);
        $payload = $list->toArray();
        $this->assertSame(20, $payload['services'][0]['rootParentId']);
        $this->assertSame(20, $payload['services'][0]['parentId']);
    }
}
