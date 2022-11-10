<?php

namespace BO\Slim\Tests;

use BO\Slim\Bootstrap;
use Twig\TwigFilter;

class FilterTest extends Base
{

    protected $classname = "Filter";

    protected $arguments = [ ];

    protected $parameters = [ ];

    protected $sessionData = [ ];

    public function testRendering()
    {
        $filter = new TwigFilter('stripslashes', function ($string) {
            return stripslashes($string);
        });
        Bootstrap::addTwigFilter($filter);
        $response = $this->render($this->arguments, $this->parameters, $this->sessionData);
        $this->assertStringContainsString('filter test: Das ist ein Test mit Slashes', (string) $response->getBody());
    }
}
