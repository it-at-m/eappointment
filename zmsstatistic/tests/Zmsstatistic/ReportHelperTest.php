<?php

namespace BO\Zmsstatistic\Tests;

use BO\Zmsstatistic\Helper\ReportHelper;

class ReportHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetGroupByForDateRangeUsesDayUpToOneMonth(): void
    {
        $helper = new ReportHelper();

        $this->assertSame('day', $helper->getGroupByForDateRange('2016-04-01', '2016-04-30'));
        $this->assertSame('day', $helper->getGroupByForDateRange('2016-04-01', '2016-05-01'));
    }

    public function testGetGroupByForDateRangeUsesMonthForLongerRanges(): void
    {
        $helper = new ReportHelper();

        $this->assertSame('month', $helper->getGroupByForDateRange('2016-04-01', '2016-05-02'));
        $this->assertSame('month', $helper->getGroupByForDateRange('2015-12-31', '2016-04-01'));
        $this->assertSame('month', $helper->getGroupByForDateRange('2025-10-31', '2026-08-20'));
    }
}
