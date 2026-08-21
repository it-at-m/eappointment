<?php

namespace BO\Slim\Tests;

use PHPUnit\Framework\TestCase;

class DateTimeFormatTest extends TestCase
{

    public function testDateTimeFormat()
    {
        $request = \BO\Slim\Tests\Base::createBasicRequest('GET', '/unittest/');
        $exception = new \BO\Slim\Exception\SessionFailed();
        $before = time();
        $infoArr = \BO\Slim\TwigExceptionHandler::getExtendedExceptionInfo($exception, $request);
        $after = time();
        $serverTime = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $infoArr['servertime'],
            new \DateTimeZone('Europe/Berlin')
        );
        $this->assertInstanceOf(\DateTimeImmutable::class, $serverTime);
        $this->assertGreaterThanOrEqual($before, $serverTime->getTimestamp());
        $this->assertLessThanOrEqual($after, $serverTime->getTimestamp());
    }

    public function testTwigDateFormat()
    {
        $twigExtensionsClass = \App::$slim
            ->getContainer()
            ->get('view')
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        $date = new \StdClass();
        $date->year = 2016;
        $date->month = 4;
        $date->day = 1;
        $this->assertEquals('2016-04-01', $twigExtensionsClass->formatDateTime($date)['ymd']);
        $this->assertEquals('Fr., 01. April 2016', $twigExtensionsClass->formatDateTime($date)['date']);
        $this->assertEquals('Freitag, den 01. April 2016', $twigExtensionsClass->formatDateTime($date)['fulldate']);
        $this->assertEquals('1459461600', $twigExtensionsClass->formatDateTime($date)['ts']);
        $this->assertEquals('noroute', $twigExtensionsClass->currentRoute()['name']);
        $this->assertNotEquals('version.unknown', $twigExtensionsClass->currentVersion());
    }
}
