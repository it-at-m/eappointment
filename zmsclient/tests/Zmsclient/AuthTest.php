<?php

namespace BO\Zmsclient\Tests;

class AuthTest extends Base
{
    /**
     * @runInSeparateProcess
     */
    public function testBasic()
    {
        try {
            $this->assertNull(\BO\Zmsclient\Auth::getKey(), "Initial key should be null");
            \BO\Zmsclient\Auth::setKey(123456);
            $this->assertEquals(123456, \BO\Zmsclient\Auth::getKey(), "Key should be set to 123456");
            \BO\Zmsclient\Auth::removeKey();
            $this->assertEquals("", \BO\Zmsclient\Auth::getKey(), "Key should be removed");
        } catch (\Exception $e) {
            $this->fail("Exception caught in test: " . $e->getMessage());
        }
    }
}

