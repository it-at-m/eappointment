<?php

namespace BO\Zmsdb\Tests\Helper;

use BO\Zmsdb\Tests\Base;
use BO\Zmsdb\Helper\PopulateOverallCalendar;
use BO\Zmsdb\OverallCalendar;
use DateTimeImmutable;

class PopulateOverallCalendarTest extends Base
{
    protected $helper;
    public $testNow; // Umbenennung von $now zu $testNow
    protected $scopeId = 141;

    public function setUp(): void
    {
        parent::setUp();
        $this->helper = new PopulateOverallCalendar(true);
        $this->testNow = new DateTimeImmutable('2016-05-01 10:00:00'); // Verwendung von $testNow
    }

    public function testWriteClosedRaster(): void
    {
        $calendar = new OverallCalendar();
        $this->assertFalse(
            $calendar->existsToday($this->scopeId),
            'Calendar should be empty initially'
        );

        $m = new \ReflectionMethod(PopulateOverallCalendar::class, 'writeClosedRaster');
        $m->setAccessible(true);
        $m->invoke($this->helper, $this->scopeId, $this->testNow); // Verwendung von $testNow

        $this->assertTrue(
            $calendar->existsToday($this->scopeId),
            'Calendar should have entries after writeClosedRaster'
        );

        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $count = $pdo->fetchColumn(
            'SELECT COUNT(*) FROM gesamtkalender WHERE scope_id = ? AND DATE(time) = ?',
            [$this->scopeId, $this->testNow->format('Y-m-d')] // Verwendung von $testNow
        );
        $this->assertEquals(288, $count, 'Should create 288 five-minute slots');

        $closed = $pdo->fetchColumn(
            'SELECT COUNT(*) FROM gesamtkalender WHERE scope_id = ? AND DATE(time) = ? AND status = "closed"',
            [$this->scopeId, $this->testNow->format('Y-m-d')] // Verwendung von $testNow
        );
        $this->assertEquals(288, $closed, 'All slots should be closed');
    }

    public function testUpdateFreeByAvailabilities(): void
    {
        $m1 = new \ReflectionMethod(PopulateOverallCalendar::class, 'writeClosedRaster');
        $m1->setAccessible(true);
        $m1->invoke($this->helper, $this->scopeId, $this->testNow); // Verwendung von $testNow

        $m2 = new \ReflectionMethod(PopulateOverallCalendar::class, 'updateFreeByAvailabilities');
        $m2->setAccessible(true);
        $m2->invoke($this->helper, $this->scopeId, $this->testNow); // Verwendung von $testNow

        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $free = $pdo->fetchColumn(
            'SELECT COUNT(*) FROM gesamtkalender WHERE scope_id = ? AND DATE(time) = ? AND status = "free"',
            [$this->scopeId, $this->testNow->format('Y-m-d')] // Verwendung von $testNow
        );
        $this->assertGreaterThan(0, $free, 'Some slots should be marked free');
    }

    public function testWriteCalendar(): void
    {
        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $pdo->exec('DELETE FROM gesamtkalender');

        $this->helper->writeCalendar($this->testNow); // Verwendung von $testNow

        $total = $pdo->fetchColumn('SELECT COUNT(*) FROM gesamtkalender');
        $this->assertGreaterThan(0, $total, 'Calendar should be populated');

        $scopeCount = $pdo->fetchColumn('SELECT COUNT(DISTINCT scope_id) FROM gesamtkalender');
        $this->assertGreaterThan(0, $scopeCount, 'Multiple scopes should be processed');
    }
}
