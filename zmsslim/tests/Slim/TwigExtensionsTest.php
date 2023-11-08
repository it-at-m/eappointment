<?php

namespace BO\Slim\Tests;

use PHPUnit\Framework\TestCase;

class TwigExtensionsTest extends TestCase
{

    public function testBasic()
    {
        $twigExtensionsClass = \App::$slim
            ->getContainer()
            ->get('view')
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
        $this->assertEquals(
            '/unittest/123/?lang=en',
            $twigExtensionsClass->urlGet('getroute', ['id' => 123], ['lang' => 'en'])
        );
    }

    public function testCurrentRouteEn()
    {
        \App::$slim->getContainer()['currentRoute'] = 'unittest';
        \App::$slim->getContainer()['currentRouteParams'] = ['id' => 123, 'lang' => 'en'];

        $twigExtensionsClass = \App::$slim
            ->getContainer()
            ->get('view')
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        
        $this->assertEquals('unittest', $twigExtensionsClass->currentRoute('en')['name']);
        $this->assertArrayHasKey('lang', $twigExtensionsClass->currentRoute('en')['params']);
        $this->assertArrayNotHasKey('lang', $twigExtensionsClass->currentRoute('de')['params']);
    }

    public function testCurrentRouteDe()
    {
        \App::$slim->getContainer()['currentRoute'] = 'unittest';
        \App::$slim->getContainer()['currentRouteParams'] = ['id' => 123, 'lang' => 'de'];

        $twigExtensionsClass = \App::$slim
            ->getContainer()
            ->get('view')
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
        
        $this->assertEquals('unittest', $twigExtensionsClass->currentRoute('de')['name']);
        $this->assertArrayNotHasKey('lang', $twigExtensionsClass->currentRoute('de')['params']);
    }
}
