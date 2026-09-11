<?php

namespace BO\Zmsstatistic\Tests;

use BO\Zmsstatistic\Helper\ReportHelper;
use PHPUnit\Framework\Attributes\DataProvider;

class ReportHelperFormatTimeValueTest extends \PHPUnit\Framework\TestCase
{
    #[DataProvider('timeValueProvider')]
    public function testFormatTimeValue(mixed $value, mixed $expected): void
    {
        $this->assertSame($expected, ReportHelper::formatTimeValue($value));
    }

    public static function timeValueProvider(): array
    {
        return [
            'non-numeric passthrough' => ['-', '-'],
            'zero' => [0, '00:00'],
            'nearest second' => [1.11, '01:07'],
            'half-second float trap' => [3.425, '03:26'],
            'unrounded overall average' => [(62 + 132 + 32) / 3 / 60, '01:15'],
        ];
    }
}
