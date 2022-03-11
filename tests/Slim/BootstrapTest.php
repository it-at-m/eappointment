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

}
