<?php

namespace BO\Slim\Tests\Slim\Middleware;

use BO\Slim\Middleware\OAuthMiddleware;
use PHPUnit\Framework\TestCase;


class OAuthMiddlewareTest extends TestCase
{
    public function testAuthInstancesContainsKeycloak()
    {
        $this->assertArrayHasKey('keycloak', OAuthMiddleware::$authInstances);
    }

    public function testAuthInstancesDoesNotContainUnknown()
    {
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

    public function testKeycloakAuthInstanceExists()
    {
        $this->assertTrue(isset(OAuthMiddleware::$authInstances['keycloak']));
    }

    public function testUnknownAuthInstanceDoesNotExist()
    {
        $this->assertFalse(isset(OAuthMiddleware::$authInstances['unknown']));
    }

    public function testAuthInstancesIsArray()
    {
        $this->assertIsArray(OAuthMiddleware::$authInstances);
    }

    public function testAuthInstancesIsNotEmpty()
    {
        $this->assertNotEmpty(OAuthMiddleware::$authInstances);
    }
}
