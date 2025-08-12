<?php

namespace BO\Slim\Tests\Slim\Middleware;

use BO\Slim\Middleware\OAuthMiddleware;
use PHPUnit\Framework\TestCase;


class OAuthMiddlewareTest extends TestCase
{
    public function testProviderValidationWithAuthInstances()
    {
        $this->assertArrayHasKey('keycloak', OAuthMiddleware::$authInstances);
        $this->assertArrayNotHasKey('unknown', OAuthMiddleware::$authInstances);
    }

    public function testConstructorWithLoginHandler()
    {
        $loginMiddleware = new OAuthMiddleware('login');
        $this->assertInstanceOf(OAuthMiddleware::class, $loginMiddleware);
    }

    public function testConstructorWithLogoutHandler()
    {
        $logoutMiddleware = new OAuthMiddleware('logout');
        $this->assertInstanceOf(OAuthMiddleware::class, $logoutMiddleware);
    }

    public function testConstructorWithRefreshHandler()
    {
        $refreshMiddleware = new OAuthMiddleware('refresh');
        $this->assertInstanceOf(OAuthMiddleware::class, $refreshMiddleware);
    }

    public function testEnhancedProviderValidationLogic()
    {
        $this->assertTrue(isset(OAuthMiddleware::$authInstances['keycloak']));
        $this->assertFalse(isset(OAuthMiddleware::$authInstances['unknown']));
    }

    public function testAuthInstancesArrayStructure()
    {
        $this->assertIsArray(OAuthMiddleware::$authInstances);
        $this->assertNotEmpty(OAuthMiddleware::$authInstances);
    }
}
