<?php

namespace BO\Dldb\Tests;

use PHPUnit\Framework\TestCase;

class DateTimeFormatTest extends TestCase
{

    public function testTwigDateFormat()
    {
        $twigExtensionsClass = new \BO\Dldb\TwigExtension();
        $date = '2016-04-01';
        $this->assertEquals('2016-04-01', $twigExtensionsClass->formatDateTime($date)['dateId']);
        $this->assertEquals('Fr., 01. April 2016', $twigExtensionsClass->formatDateTime($date)['date']);
        $this->assertEquals('Freitag, den 01. April 2016', $twigExtensionsClass->formatDateTime($date)['fulldate']);
        $this->assertEquals('1459461600', $twigExtensionsClass->formatDateTime($date)['ts']);
        $this->assertEquals(4, $twigExtensionsClass->formatDateTime($date)['weekday']);
        $this->assertEquals('Freitag', $twigExtensionsClass->formatDateTime($date)['weekdayfull']);
        $this->assertEquals('11:55 Uhr', $twigExtensionsClass->formatDateTime($date . ' 11:55:00')['time']);
        $this->assertEquals('11:55', $twigExtensionsClass->formatDateTime($date . ' 11:55:00')['timeId']);
    }
}
