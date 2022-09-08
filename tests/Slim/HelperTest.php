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

        $hash = Helper::hashQueryParameters(['id' => '123'], ['id']);

        self::assertSame('kD9aS4T8ecbTSuNL', $hash);
    }
}