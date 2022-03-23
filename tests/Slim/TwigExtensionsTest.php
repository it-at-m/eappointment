<?php

namespace BO\Slim\Tests;

use PHPUnit\Framework\TestCase;

class TwigExtensionsTest extends TestCase
{

    public function testBasic()
    {
        $twigExtensionsClass = \App::$slim
            ->getContainer()->view
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        $this->assertEquals('boslimExtension', $twigExtensionsClass->getName());
        $this->assertTrue($twigExtensionsClass->isNumeric(5));
        $this->assertFalse($twigExtensionsClass->isNumeric('test'));
        $this->assertEquals(\App::$now, $twigExtensionsClass->getNow());
        \App::$now = null;
        $this->assertEquals(
            (new \DateTimeImmutable())->format('yy-mm-dd'),
            $twigExtensionsClass->getNow()->format('yy-mm-dd')
        );
        $this->assertFalse($twigExtensionsClass->getSystemStatus('APP_ENV'));
        $this->assertEquals('unittest', $twigExtensionsClass->toTextFormat('<span>unit<br />test</span>'));

        $date = new \StdClass();
        $date->year = 2016;
        $date->month = 4;
        $date->day = 1;
        $this->assertEquals('2016-04-01', $twigExtensionsClass->formatDateTime($date)['ymd']);
        $this->assertEquals('1459461600', $twigExtensionsClass->formatDateTime($date)['ts']);
        $this->assertEquals('noroute', $twigExtensionsClass->currentRoute()['name']);
    }

    public function testCurrentRouteEn()
    {
        $route = new \Slim\Route('GET', '/unittest/{id}/{lang}/', []);
        $route->setName('unittest');
        $route->setArguments(['id' => 123, 'lang' => 'en']);
        \App::$slim->getContainer()['currentRoute'] = $route->getName();
        \App::$slim->getContainer()['currentRouteParams'] = $route->getArguments();

        $twigExtensionsClass = \App::$slim
            ->getContainer()->view
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        
        $this->assertEquals('unittest', $twigExtensionsClass->currentRoute('en')['name']);
        $this->assertArrayHasKey('lang', $twigExtensionsClass->currentRoute('en')['params']);
        $this->assertArrayNotHasKey('lang', $twigExtensionsClass->currentRoute('de')['params']);
    }

    public function testCurrentRouteDe()
    {
        $route = new \Slim\Route('GET', '/unittest/{id}/{lang}/', []);
        $route->setName('unittest');
        $route->setArguments(['id' => 123, 'lang' => 'de']);
        \App::$slim->getContainer()['currentRoute'] = $route->getName();
        \App::$slim->getContainer()['currentRouteParams'] = $route->getArguments();

        $twigExtensionsClass = \App::$slim
            ->getContainer()->view
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        
        $this->assertEquals('unittest', $twigExtensionsClass->currentRoute('de')['name']);
        $this->assertArrayNotHasKey('lang', $twigExtensionsClass->currentRoute('de')['params']);
    }
}
