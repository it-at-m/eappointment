<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Utils;

use BO\Zmscitizenbackend\Utils\SourceNames;
use PHPUnit\Framework\TestCase;

class SourceNamesTest extends TestCase
{
    private string $previousSourceName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousSourceName = \App::$source_name;
    }

    protected function tearDown(): void
    {
        \App::$source_name = $this->previousSourceName;
        parent::tearDown();
    }

    public function testConfiguredSplitsAndDeduplicates(): void
    {
        \App::$source_name = 'dldb, zms;dldb|unittest';
        $this->assertSame(['dldb', 'zms', 'unittest'], SourceNames::configured());
    }

    public function testConfiguredFallsBackToDldb(): void
    {
        \App::$source_name = '';
        $this->assertSame(['dldb'], SourceNames::configured());
    }
}
