<?php

namespace BO\Zmsclient\Tests\Zmsclient;

use PHPUnit\Framework\TestCase;
use BO\Zmsclient\Auth;
use App;

/**
 * Test class for BO\Zmsclient\Auth
 */
class AuthTest extends TestCase
{
    private $originalCookies;
    private $originalHeadersSent;
    private $mockLog;

    protected function setUp(): void
    {
        // Store original state
        $this->originalCookies = $_COOKIE ?? [];
        $this->originalHeadersSent = headers_sent();
        
        // Clear cookies for clean test state
        $_COOKIE = [];

    }

    protected function tearDown(): void
    {
        // Restore original state
        $_COOKIE = $this->originalCookies;
        
        // Clean up any cookies that might have been set
        if (isset($_COOKIE[Auth::getCookieName()])) {
            unset($_COOKIE[Auth::getCookieName()]);
        }
        if (isset($_COOKIE['OIDC'])) {
            unset($_COOKIE['OIDC']);
        }
    }

    public function testSetKeyBasic()
    {
        $authKey = 'test-auth-key-123';
        $expires = time() + 3600;
        
        Auth::setKey($authKey, $expires);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testSetKeyWithZeroExpires()
    {
        $authKey = 'test-auth-key-zero';
        
        Auth::setKey($authKey, 0);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testSetKeyWithNegativeExpires()
    {
        $authKey = 'test-auth-key-negative';
        $expires = time() - 3600;
        
        Auth::setKey($authKey, $expires);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testSetKeyWithLongAuthKey()
    {
        $authKey = str_repeat('a', 1000);
        $expires = time() + 3600;
        
        Auth::setKey($authKey, $expires);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testSetKeyWithSpecialCharacters()
    {
        $authKey = 'test-key-with-special-chars!@#$%^&*()_+-=[]{}|;:,.<>?';
        $expires = time() + 3600;
        
        Auth::setKey($authKey, $expires);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testSetKeyWithEmptyString()
    {
        $authKey = '';
        $expires = time() + 3600;
        
        Auth::setKey($authKey, $expires);
        
        $this->assertEquals($authKey, $_COOKIE[Auth::getCookieName()]);
    }

    public function testGetKeyWhenCookieExists()
    {
        $authKey = 'test-get-key';
        $_COOKIE[Auth::getCookieName()] = $authKey;
        
        $result = Auth::getKey();
        
        $this->assertEquals($authKey, $result);
    }

    public function testGetKeyWhenCookieNotExists()
    {
        $result = Auth::getKey();
        
        $this->assertNull($result);
    }

    public function testGetKeyWithEmptyCookie()
    {
        $_COOKIE[Auth::getCookieName()] = '';
        
        $result = Auth::getKey();
        
        $this->assertEquals('', $result);
    }

    public function testRemoveKeyWhenCookieExists()
    {
        $authKey = 'test-remove-key';
        $_COOKIE[Auth::getCookieName()] = $authKey;
        
        Auth::removeKey();
        
        $this->assertArrayNotHasKey(Auth::getCookieName(), $_COOKIE);
    }

    public function testRemoveKeyWhenCookieNotExists()
    {
        // Should not throw any errors
        Auth::removeKey();
        
        $this->assertArrayNotHasKey(Auth::getCookieName(), $_COOKIE);
    }

    public function testRemoveKeyLogsInfoWhenAppLogExists()
    {
        $authKey = 'test-remove-key-logging';
        $_COOKIE[Auth::getCookieName()] = $authKey;
        
        $this->mockLog
            ->expects($this->once())
            ->method('info')
            ->with(
                'Auth session removed',
                $this->callback(function ($context) use ($authKey) {
                    return $context['event'] === 'auth_session_removed' &&
                           $context['hashed_session_token'] === hash('sha256', $authKey) &&
                           isset($context['timestamp']);
                })
            );
        
        Auth::removeKey();
    }

    public function testRemoveKeyDoesNotLogWhenAppLogNotExists()
    {
        // Temporarily remove log
        $originalLog = App::$log;
        App::$log = null;
        
        $authKey = 'test-remove-key-no-log';
        $_COOKIE[Auth::getCookieName()] = $authKey;
        
        // Should not throw any errors
        Auth::removeKey();
        
        $this->assertArrayNotHasKey(Auth::getCookieName(), $_COOKIE);
        
        // Restore log
        App::$log = $originalLog;
    }

    public function testGetCookieName()
    {
        $cookieName = Auth::getCookieName();
        
        $this->assertEquals('X-AuthKey', $cookieName);
    }

    public function testGetOidcName()
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('getOidcName');
        $method->setAccessible(true);
        
        $oidcName = $method->invoke(null);
        
        $this->assertEquals('OIDC', $oidcName);
    }

    public function testSetOidcProviderBasic()
    {
        $provider = 'keycloak';
        
        Auth::setOidcProvider($provider);
        
        $this->assertEquals($provider, $_COOKIE['OIDC']);
    }

    public function testSetOidcProviderWithEmptyString()
    {
        $provider = '';
        
        Auth::setOidcProvider($provider);
        
        $this->assertEquals($provider, $_COOKIE['OIDC']);
    }

    public function testSetOidcProviderWithSpecialCharacters()
    {
        $provider = 'provider-with-special-chars!@#$%^&*()';
        
        Auth::setOidcProvider($provider);
        
        $this->assertEquals($provider, $_COOKIE['OIDC']);
    }

    public function testGetOidcProviderWhenCookieExists()
    {
        $provider = 'test-oidc-provider';
        $_COOKIE['OIDC'] = $provider;
        
        $result = Auth::getOidcProvider();
        
        $this->assertEquals($provider, $result);
    }

    public function testGetOidcProviderWhenCookieNotExists()
    {
        $result = Auth::getOidcProvider();
        
        $this->assertFalse($result);
    }

    public function testGetOidcProviderWithEmptyCookie()
    {
        $_COOKIE['OIDC'] = '';
        
        $result = Auth::getOidcProvider();
        
        $this->assertEquals('', $result);
    }

    public function testRemoveOidcProviderWhenCookieExists()
    {
        $provider = 'test-remove-oidc';
        $_COOKIE['OIDC'] = $provider;
        
        Auth::removeOidcProvider();
        
        $this->assertArrayNotHasKey('OIDC', $_COOKIE);
    }

    public function testRemoveOidcProviderWhenCookieNotExists()
    {
        // Should not throw any errors
        Auth::removeOidcProvider();
        
        $this->assertArrayNotHasKey('OIDC', $_COOKIE);
    }

    public function testRemoveOidcProviderWithEmptyCookie()
    {
        $_COOKIE['OIDC'] = '';
        
        Auth::removeOidcProvider();
        
        $this->assertArrayNotHasKey('OIDC', $_COOKIE);
    }

    public function testMultipleCookieOperations()
    {
        // Test setting multiple cookies
        Auth::setKey('auth-key-1', time() + 3600);
        Auth::setOidcProvider('provider-1');
        
        $this->assertEquals('auth-key-1', $_COOKIE[Auth::getCookieName()]);
        $this->assertEquals('provider-1', $_COOKIE['OIDC']);
        
        // Test removing one cookie
        Auth::removeKey();
        
        $this->assertArrayNotHasKey(Auth::getCookieName(), $_COOKIE);
        $this->assertEquals('provider-1', $_COOKIE['OIDC']);
        
        // Test removing the other cookie
        Auth::removeOidcProvider();
        
        $this->assertArrayNotHasKey('OIDC', $_COOKIE);
    }

    public function testCookieNameIsConstant()
    {
        $cookieName1 = Auth::getCookieName();
        $cookieName2 = Auth::getCookieName();
        
        $this->assertEquals($cookieName1, $cookieName2);
        $this->assertEquals('X-AuthKey', $cookieName1);
    }

    public function testOidcNameIsConstant()
    {
        // Use reflection to access protected method
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('getOidcName');
        $method->setAccessible(true);
        
        $oidcName1 = $method->invoke(null);
        $oidcName2 = $method->invoke(null);
        
        $this->assertEquals($oidcName1, $oidcName2);
        $this->assertEquals('OIDC', $oidcName1);
    }
}
