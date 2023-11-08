<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi\Tests\Response;

use BO\Slim\Request;
use BO\Zmsapi\Response\Message;
use BO\Zmsdb\Connection\Select;
use BO\Zmsentities\Metaresult;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

class MessageTest extends TestCase
{
    public function testJsonSerialize()
    {
        self::assertNotNull(Select::getWriteConnection());

        $uri = new Uri('http', 'localhost', 80, '/admin/account/', '', '', 'username', 'secret');
        $request = new Request('GET', $uri, new Headers([]), [], [], new Stream(fopen('php://temp', 'wb+')));
        $message = Message::create($request);
        $msgData = $message->jsonSerialize();

        self::assertInstanceOf(Metaresult::class, $msgData['meta']);
        self::assertStringContainsString(
            "https://schema.berlin.de/queuemanagement/metaresult.json",
            serialize($msgData['meta']->jsonSerialize())
        );
    }
}
