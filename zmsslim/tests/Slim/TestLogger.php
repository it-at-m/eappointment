<?php

declare(strict_types=1);

namespace BO\Slim\Tests;

use BO\Slim\LoggerService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TestLogger extends LoggerService
{
    private static ?TestCase $testCase = null;
    private static array $expectedLogs = [];

    public static function init(): void
    {
    }

    public static function initTest(TestCase $testCase): void
    {
        self::$testCase = $testCase;
        self::$expectedLogs = [];
    }

    public static function expectLogInfo(string $message, array $context = []): void
    {
        self::$expectedLogs[] = ['info', $message, $context];
    }

    public static function expectLogError(\Throwable $exception): void
    {
        self::$expectedLogs[] = ['error', $exception];
    }

    public static function logInfo(string $message, array $context = []): void
    {
        if (self::$testCase === null) {
            throw new \RuntimeException('Test case not initialized. Call initTest() first.');
        }

        $expected = current(self::$expectedLogs);
        if (!$expected) {
            self::$testCase->fail('Unexpected log info: ' . $message);
        }

        array_shift(self::$expectedLogs);
        self::$testCase->assertEquals('info', $expected[0]);
        self::$testCase->assertStringContainsString($expected[1], $message);
        if (!empty($expected[2])) {
            foreach ($expected[2] as $key => $value) {
                self::$testCase->assertArrayHasKey($key, $context);
                self::$testCase->assertEquals($value, $context[$key]);
            }
        }
    }

    public static function logError(
        \Throwable $exception,
        ?RequestInterface $request = null,
        ?ResponseInterface $response = null,
        array $context = []
    ): void {
        $expected = array_shift(self::$expectedLogs);
        if (!$expected) {
            self::$testCase->fail('Unexpected log error: ' . $exception->getMessage());
        }
        self::$testCase->assertEquals('error', $expected[0]);
        self::$testCase->assertInstanceOf(get_class($expected[1]), $exception);
    }

    public static function logRequest(ServerRequestInterface $request, ResponseInterface $response): void
    {
    }

    public static function logWarning(string $message, array $context = []): void
    {
    }

    public static function verifyNoMoreLogs(): void
    {
        self::$testCase->assertEmpty(self::$expectedLogs, 'Expected no more logs');
    }

    public static function resetTest(): void
    {
        self::$testCase = null;
        self::$expectedLogs = [];
    }
}
