<?php

namespace BO\Slim\Tests;

class BootstrapTest extends Base
{

    protected $classname = "Get";

    public function testInit()
    {
        \BO\Slim\Bootstrap::init();
        \BO\Slim\Profiler::addMemoryPeak();
        $this->assertStringContainsString('Init=', \BO\Slim\Profiler::getList());
        $this->assertStringContainsString(';Mem', \BO\Slim\Profiler::getList());
    }

    public function testWithoutInit()
    {
        $instance = \BO\Slim\Bootstrap::getInstance();
        $this->assertTrue($instance instanceof \BO\Slim\Bootstrap);
    }

    public function testTwigView()
    {
        $twigView = \BO\Slim\Bootstrap::getTwigView();
        var_dump($twigView->getTemplatePath());
        $this->assertTrue($instance instanceof \BO\Slim\Bootstrap);
    }
}
