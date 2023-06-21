<?php

namespace BO\Slim\Tests;

use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testInit()
    {
        \BO\Slim\Bootstrap::init();
        \BO\Slim\Profiler::addMemoryPeak();
        $this->assertStringContainsString('Init=', \BO\Slim\Profiler::getList());
        $this->assertStringContainsString(';Mem', \BO\Slim\Profiler::getList());
    }

    public function testTwigView()
    {
        $twigView = \BO\Slim\Bootstrap::getTwigView();
        $this->assertStringContainsString('tests/Slim/templates', $twigView->getLoader()->getPaths()[0]);
        $this->assertTrue(is_dir(\App::APP_PATH . \App::TWIG_CACHE));
    }

    public function testWithTemplateDirectory()
    {
        \BO\Slim\Bootstrap::init();
        \BO\Slim\Bootstrap::addTwigTemplateDirectory('dldb', \App::APP_PATH . '/Slim/templates/dldb/');
        $twigView = \App::$slim->getContainer()->get('view');
        $this->assertStringContainsString('templates/dldb', $twigView->getLoader()->getPaths('dldb')[0]);
    }
}
