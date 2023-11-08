<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Tests;

use BO\Slim\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testHashQueryParameters()
    {
        \App::$urlSignatureSecret = 'testSecret';

        $hash = Helper::hashQueryParameters('testNameOrUrl', ['testParam' => '123', 'a' => 'b'], ['testParam']);
        self::assertSame('CewTvYofNPqG4yS4', $hash);

        $hash = Helper::hashQueryParameters('testNameOrUrl', ['testParam' => '123', 'a' => []], ['testParam', 'a']);
        self::assertSame('CewTvYofNPqG4yS4', $hash);

        $hash = Helper::hashQueryParameters('testNameOrUrl', ['testParam' => '123', 'a' => ''], ['testParam', 'a']);
        self::assertSame('CewTvYofNPqG4yS4', $hash);

        $hash = Helper::hashQueryParameters('testNameOrUrl', ['testParam' => '123', 'a' => 'b'], ['testParam', 'a']);
        self::assertSame('z0tkACuP1Ej0TCAb', $hash);

        $hash = Helper::hashQueryParameters('testNameOrUrl', ['testParam' => '123', 'a' => ['b']], ['testParam', 'a']);
        self::assertSame('z0tkACuP1Ej0TCAb', $hash);
    }
}
