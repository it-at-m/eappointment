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
            error_log("Testing Auth::getKey() - Expecting null initially.");
            $this->assertNull(\BO\Zmsclient\Auth::getKey(), "Initial key should be null");
            error_log("Setting key to 123456.");
            \BO\Zmsclient\Auth::setKey(123456);
            error_log("Testing Auth::getKey() - Expecting 123456.");
            $this->assertEquals(123456, \BO\Zmsclient\Auth::getKey(), "Key should be set to 123456");
            error_log("Removing key.");
            \BO\Zmsclient\Auth::removeKey();
            error_log("Testing Auth::getKey() - Expecting empty string.");
            $this->assertEquals("", \BO\Zmsclient\Auth::getKey(), "Key should be removed");
        } catch (\Exception $e) {
            error_log("Exception caught in AuthTest::testBasic: " . $e->getMessage());
            $this->fail("Exception caught in test: " . $e->getMessage());
        }
    }
}
