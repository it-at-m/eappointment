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
            error_log("Entering testBasic method.");
            $this->assertNull(\BO\Zmsclient\Auth::getKey(), "Initial key should be null");
            error_log("Key is initially null as expected.");

            \BO\Zmsclient\Auth::setKey(123456);
            error_log("Key set to 123456.");

            $this->assertEquals(123456, \BO\Zmsclient\Auth::getKey(), "Key should be set to 123456");
            error_log("Key retrieval after set confirms correct value.");

            \BO\Zmsclient\Auth::removeKey();
            error_log("Key removed.");

            $this->assertEquals("", \BO\Zmsclient\Auth::getKey(), "Key should be removed");
            error_log("Key retrieval after removal confirms empty string.");

        } catch (\Exception $e) {
            error_log("Exception caught in testBasic: " . $e->getMessage());
            error_log("Stack Trace: " . $e->getTraceAsString());
            $this->fail("Exception caught in test: " . $e->getMessage());
        }
        error_log("Exiting testBasic method.");
    }
}
