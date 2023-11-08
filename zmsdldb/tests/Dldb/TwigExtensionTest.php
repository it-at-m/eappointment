<?php

namespace BO\Dldb\Tests;

use PHPUnit\Framework\TestCase;

class TwigExtensionTest extends TestCase
{

    public function testBasic()
    {
        $twigExtensionsClass = new \BO\Dldb\TwigExtension();
        $this->assertEquals('dldb', $twigExtensionsClass->getName());
        $this->assertEquals('<pre>unittest</pre>', $twigExtensionsClass->dump('unittest'));
        $this->assertEquals('Montag', $twigExtensionsClass->convertOpeningTimes('monday'));
        $this->assertEquals('1459511700', $twigExtensionsClass->dateToTS('2016-04-01 11:55'));
        $this->assertEquals('2016-04-01', $twigExtensionsClass->tsToDate('1459511700'));
        $this->assertEquals('ec', $twigExtensionsClass->kindOfPayment(2));
    }
}
