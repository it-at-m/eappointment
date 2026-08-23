<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Availability\Repository;

use BO\Zmscitizenbackend\Availability\Model\AvailableCalendar;
use BO\Zmscitizenbackend\Availability\Repository\AvailableCalendarRepository;
use PHPUnit\Framework\TestCase;

class AvailableCalendarRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        AvailableCalendarRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(AvailableCalendarRepository::class);
        AvailableCalendarRepository::use($override);
        $this->assertSame($override, AvailableCalendarRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(AvailableCalendarRepository::class);
        $override->method('readAvailableCalendar')->willReturn(
            new AvailableCalendar('2024-08-21', '2024-08-23')
        );
        AvailableCalendarRepository::use($override);
        AvailableCalendarRepository::use(null);

        $created = AvailableCalendarRepository::create();
        $this->assertInstanceOf(AvailableCalendarRepository::class, $created);
        $this->assertNotSame($override, $created);
    }
}
