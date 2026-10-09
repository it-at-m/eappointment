<?php

declare(strict_types=1);

namespace BO\Zmsentities\Tests;

use BO\Zmsentities\Helper\SlotWindow;
use PHPUnit\Framework\TestCase;

class SlotWindowTest extends TestCase
{
    public function testSixtyToNinetyShortensTheWindowToWholeSlots(): void
    {
        $end = SlotWindow::fittedEndTime('08:00:00', '12:00:00', 90);

        $this->assertSame('11:00:00', $end);
        $this->assertLessThanOrEqual(
            SlotWindow::minutes('12:00:00') - SlotWindow::minutes('08:00:00'),
            SlotWindow::minutes((string) $end) - SlotWindow::minutes('08:00:00')
        );
        $this->assertSame(0, (SlotWindow::minutes((string) $end) - SlotWindow::minutes('08:00:00')) % 90);
    }

    public function testExactMultipleKeepsTheSameEnd(): void
    {
        $this->assertSame('17:00:00', SlotWindow::fittedEndTime('08:00:00', '17:00:00', 90));
    }

    public function testShorterSlotKeepsTheWindowWhenItStillDivides(): void
    {
        $this->assertSame('11:00:00', SlotWindow::fittedEndTime('08:00', '11:00', 60));
    }

    public function testRemainderIsDroppedSoTheWindowDoesNotGrow(): void
    {
        $this->assertSame('12:00:00', SlotWindow::fittedEndTime('08:00:00', '12:20:00', 30));
        $this->assertSame('12:00:00', SlotWindow::fittedEndTime('08:00:00', '12:10:00', 60));
    }

    public function testNewSlotLongerThanTheWindowDoesNotFit(): void
    {
        $this->assertNull(SlotWindow::fittedEndTime('08:00:00', '09:00:00', 90));
    }

    public function testUnusedAndInvalidWindowsDoNotFit(): void
    {
        $this->assertTrue(SlotWindow::isUnused('00:00:00', '00:00:00'));
        $this->assertNull(SlotWindow::fittedEndTime('00:00:00', '00:00:00', 60));
        $this->assertNull(SlotWindow::fittedEndTime('12:00:00', '08:00:00', 60));
        $this->assertNull(SlotWindow::fittedEndTime('08:00:00', '12:00:00', 0));
    }

    public function testFittedWindowNeverRunsPastTheOldEnd(): void
    {
        foreach ([15, 20, 30, 45, 60, 90, 120] as $slot) {
            $end = SlotWindow::fittedEndTime('08:00:00', '16:40:00', $slot);
            $this->assertNotNull($end);
            $this->assertLessThanOrEqual(SlotWindow::minutes('16:40:00'), SlotWindow::minutes($end));
            $this->assertSame(0, (SlotWindow::minutes($end) - SlotWindow::minutes('08:00:00')) % $slot);
        }
    }
}
