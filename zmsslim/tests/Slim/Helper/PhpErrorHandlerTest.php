<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\PhpErrorHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class PhpErrorHandlerTest extends TestCase
{
    private TestHandler $logHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logHandler = new TestHandler();
        \App::$log = new Logger('test');
        \App::$log->pushHandler($this->logHandler);
    }

    protected function tearDown(): void
    {
        \App::$log = null;
        parent::tearDown();
    }

    public function testSeverityToLogLevel(): void
    {
        $this->assertSame(Logger::WARNING, PhpErrorHandler::severityToLogLevel(E_USER_WARNING));
        $this->assertSame(Logger::NOTICE, PhpErrorHandler::severityToLogLevel(E_USER_NOTICE));
        $this->assertSame(Logger::ERROR, PhpErrorHandler::severityToLogLevel(E_USER_ERROR));
    }

    public function testHandleLogsWarningWithoutThrowing(): void
    {
        $handled = PhpErrorHandler::handle(
            E_USER_WARNING,
            'Attempt to read property "support" on null',
            __FILE__,
            __LINE__
        );

        $this->assertTrue($handled);
        $this->assertCount(1, $this->logHandler->getRecords());
        $record = $this->logHandler->getRecords()[0];
        $this->assertSame(Logger::WARNING, $record['level']);
        $this->assertSame('Attempt to read property "support" on null', $record['message']);
        $this->assertSame(E_USER_WARNING, $record['context']['php_errno']);
        $this->assertSame(__FILE__, $record['context']['file']);
    }

    public function testHandleReturnsFalseWhenErrorIsSuppressed(): void
    {
        $previousReporting = error_reporting(0);

        $handled = PhpErrorHandler::handle(E_USER_WARNING, 'suppressed warning', __FILE__, __LINE__);

        error_reporting($previousReporting);

        $this->assertFalse($handled);
        $this->assertCount(0, $this->logHandler->getRecords());
    }
}
