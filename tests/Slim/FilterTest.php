<?php

namespace BO\Slim\Tests;

class FilterTest extends Base
{

    protected $classname = "Filter";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected $sessionData = [ ];

    public function testRendering()
    {
        $filter = new \Twig_SimpleFilter('stripslashes', function ($string) {
            return stripslashes($string);
        });
        \BO\Slim\Bootstrap::addTwigFilter($filter);
        $response = $this->render($this->arguments, $this->parameters, $this->sessionData);
        $this->assertStringContainsString('filter test: Das ist ein Test mit Slashes', (string) $response->getBody());
    }
}
