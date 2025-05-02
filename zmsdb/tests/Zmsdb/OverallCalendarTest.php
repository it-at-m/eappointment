<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\OverallCalendar as Query;
use DateTimeImmutable;

class OverallCalendarTest extends Base
{
    protected $query;
    protected $scopeId = 141;

    public function setUp(): void
    {
        parent::setUp();
        $this->query = new Query();
    }

    public function testInsertClosedAndExistsToday()
    {
        $this->assertFalse($this->query->existsToday($this->scopeId));
        $time = new DateTimeImmutable('today');
        $this->query->insertClosed($this->scopeId, $time);
        $this->assertTrue($this->query->existsToday($this->scopeId));
    }

    public function testResetRange()
    {
        $start = new DateTimeImmutable('today');
        $end = (clone $start)->modify('+1 hour');
        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange($this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $this->query->resetRange($this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $status = $pdo->fetchColumn(
            'SELECT status FROM gesamtkalender WHERE scope_id = ? AND time = ?',
            [$this->scopeId, $start->format('Y-m-d H:i:s')]
        );
        $this->assertEquals('closed', $status);
    }

    public function testOpenRange()
    {
        $start = new DateTimeImmutable('today');
        $end = (clone $start)->modify('+1 hour');
        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange($this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $status = $pdo->fetchColumn(
            'SELECT status FROM gesamtkalender WHERE scope_id = ? AND time = ?',
            [$this->scopeId, $start->format('Y-m-d H:i:s')]
        );
        $this->assertEquals('free', $status);
    }

    public function testBookAndUnbook()
    {
        $start = new DateTimeImmutable('today 10:00:00');
        $end = (clone $start)->modify('+30 minutes');
        $processId = 12345;
        $slots = 3;
        $this->query->insertClosed($this->scopeId, $start);
        $this->query->openRange($this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $this->query->book($this->scopeId, $start->format('Y-m-d H:i:s'), $processId, $slots);
        $pdo = \BO\Zmsdb\Connection\Select::getReadConnection();
        $rows = $pdo->fetchAll(
            'SELECT status, process_id, slots FROM gesamtkalender WHERE scope_id = ? AND time >= ? AND time < ? ORDER BY time',
            [$this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]
        );
        $this->assertCount($slots, $rows);
        $this->assertEquals('termin', $rows[0]['status']);
        $this->assertEquals($processId, $rows[0]['process_id']);
        $this->assertEquals($slots, $rows[0]['slots']);
        $this->query->unbook($this->scopeId, $processId);
        $rows = $pdo->fetchAll(
            'SELECT status, process_id FROM gesamtkalender WHERE scope_id = ? AND time >= ? AND time < ? ORDER BY time',
            [$this->scopeId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]
        );
        foreach ($rows as $row) {
            $this->assertEquals('free', $row['status']);
            $this->assertNull($row['process_id']);
        }
    }
}
