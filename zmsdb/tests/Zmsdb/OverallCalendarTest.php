<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar as Query;
use DateTimeImmutable;

class OverallCalendarTest extends Base
{
    protected Query $query;
    protected int $scopeId = 141;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query = new Query();
    }

    public function testInsertClosedAndExistsToday(): void
    {
        $this->assertFalse(
            $this->query->existsToday($this->scopeId),
            'Calendar should be empty for today initially'
        );

        $time = new DateTimeImmutable('today');
        $this->query->insertClosed($this->scopeId, $time);

        $this->assertTrue(
            $this->query->existsToday($this->scopeId),
            'Calendar should have an entry for today after insertion'
        );
    }

    public function testResetRange(): void
    {
        $start = new DateTimeImmutable('today');
        $end = (clone $start)->modify('+1 hour');

        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange(
            $this->scopeId,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        );
        $this->query->resetRange(
            $this->scopeId,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        );

        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $status = $pdo->fetchColumn(
            'SELECT status FROM gesamtkalender WHERE scope_id = ? AND time = ?',
            [$this->scopeId, $start->format('Y-m-d H:i:s')]
        );

        $this->assertEquals('closed', $status, 'Status should be reset to closed');
    }

    public function testOpenRange(): void
    {
        $start = new DateTimeImmutable('today');
        $end = (clone $start)->modify('+1 hour');

        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange(
            $this->scopeId,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        );

        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $status = $pdo->fetchColumn(
            'SELECT status FROM gesamtkalender WHERE scope_id = ? AND time = ?',
            [$this->scopeId, $start->format('Y-m-d H:i:s')]
        );

        $this->assertEquals('free', $status, 'Status should be set to free');
    }

    public function testBookAndUnbook(): void
    {
        $start = new DateTimeImmutable('today 10:00:00');
        $end = (clone $start)->modify('+30 minutes');
        $processId = 12345;
        $units = 3;

        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange(
            $this->scopeId,
            $start->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s')
        );
        $this->query->book(
            $this->scopeId,
            $start->format('Y-m-d H:i:s'),
            $processId,
            $units
        );

        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $rows = $pdo->fetchAll(
            'SELECT status, process_id, slots FROM gesamtkalender WHERE scope_id = ? AND time >= ? AND time < ? ORDER BY time',
            [
                $this->scopeId,
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s')
            ]
        );
        $this->assertCount($units, $rows, 'Should have booked the correct number of slots');
        $this->assertEquals('termin', $rows[0]['status']);
        $this->assertEquals($processId, $rows[0]['process_id']);
        $this->assertEquals($units, $rows[0]['slots']);

        $this->query->unbook($this->scopeId, $processId);
        $post = $pdo->fetchAll(
            'SELECT status, process_id FROM gesamtkalender WHERE scope_id = ? AND time >= ? AND time < ? ORDER BY time',
            [
                $this->scopeId,
                $start->format('Y-m-d H:i:s'),
                $end->format('Y-m-d H:i:s')
            ]
        );
        foreach ($post as $r) {
            $this->assertEquals('free', $r['status']);
            $this->assertNull($r['process_id']);
        }
    }
}
