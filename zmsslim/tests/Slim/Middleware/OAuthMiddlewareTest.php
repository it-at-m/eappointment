<?php

namespace BO\Slim\Tests\Slim\Middleware;

use BO\Slim\Middleware\OAuthMiddleware;
use PHPUnit\Framework\TestCase;

class MockAuth
{
    public static function getOidcProvider() { return 'keycloak'; }
    public static function getKey() { return 'test-key'; }
    public static function setKey($key, $time = null) { return true; }
    public static function removeKey() { return true; }
    public static function setOidcProvider($provider) { return true; }
    public static function removeOidcProvider() { return true; }
}

class OAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists('App')) {
            eval('class App { public static $log; }');
        }
        \App::$log = $this->createMock(\Psr\Log\LoggerInterface::class);

        if (!class_exists('BO\Zmsclient\Auth')) {
            class_alias('BO\Slim\Tests\Slim\Middleware\MockAuth', 'BO\Zmsclient\Auth');
        }
    }

    public function testProviderValidationWithAuthInstances()
    {
        $this->assertArrayHasKey('keycloak', OAuthMiddleware::$authInstances);
        $this->assertArrayNotHasKey('unknown', OAuthMiddleware::$authInstances);
    }

    public function testConstructorWithDifferentHandlers()
    {
        $loginMiddleware = new OAuthMiddleware('login');
        $this->assertInstanceOf(OAuthMiddleware::class, $loginMiddleware);

        $logoutMiddleware = new OAuthMiddleware('logout');
        $this->assertInstanceOf(OAuthMiddleware::class, $logoutMiddleware);

        $refreshMiddleware = new OAuthMiddleware('refresh');
        $this->assertInstanceOf(OAuthMiddleware::class, $refreshMiddleware);
    }

    public function testEnhancedProviderValidationLogic()
    {
        $this->assertTrue(isset(OAuthMiddleware::$authInstances['keycloak']));
        $this->assertFalse(isset(OAuthMiddleware::$authInstances['unknown']));
    }

    public function testErrorLoggingConfiguration()
    {
        $this->assertIsArray(OAuthMiddleware::$authInstances);
        $this->assertNotEmpty(OAuthMiddleware::$authInstances);
    }
}
