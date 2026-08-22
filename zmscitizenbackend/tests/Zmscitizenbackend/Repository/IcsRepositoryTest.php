<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Repository;

use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\IcsRepository;
use PHPUnit\Framework\TestCase;

class IcsRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        IcsRepository::use(null);
        parent::tearDown();
    }

    public function testCreateReturnsOverrideWhenSet(): void
    {
        $override = $this->createStub(IcsRepository::class);
        IcsRepository::use($override);
        $this->assertSame($override, IcsRepository::create());
    }

    public function testCreateReturnsFreshRepositoryAfterReset(): void
    {
        $override = $this->createStub(IcsRepository::class);
        $override->method('readIcsContent')->willReturn('BEGIN:VCALENDAR');
        IcsRepository::use($override);
        IcsRepository::use(null);

        $created = IcsRepository::create();
        $this->assertInstanceOf(IcsRepository::class, $created);
        $this->assertNotSame($override, $created);
    }

    public function testAttachIcsSkipsDeletedAppointments(): void
    {
        $appointment = new ThinnedProcess(
            processId: 101002,
            timestamp: (string) time(),
            authKey: 'fb43',
            status: 'deleted'
        );
        IcsRepository::create()->attachIcs($appointment);
        $this->assertNull($appointment->icsContent);
    }
}
