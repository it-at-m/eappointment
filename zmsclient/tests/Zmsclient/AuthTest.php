<?php

namespace BO\Zmsclient\Tests;
error_reporting(E_ALL);
ini_set('display_errors', '1');  // For testing/debugging only

class AuthTest extends Base
{
    /**
     * @runInSeparateProcess
     */
    public function testBasic()
    {
        try {
            error_log("Current environment: " . json_encode($_ENV)); // Log environment variables
            error_log("TestBasic method entered.");
            $this->assertNull(\BO\Zmsclient\Auth::getKey(), "Initial key should be null");
            \BO\Zmsclient\Auth::setKey(123456);
            $this->assertEquals(123456, \BO\Zmsclient\Auth::getKey());
            \BO\Zmsclient\Auth::removeKey();
            $this->assertEquals("", \BO\Zmsclient\Auth::getKey());
    
            error_log("TestBasic method exiting.");
        } catch (\Exception $e) {
            error_log("Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->fail("Exception caught in test: " . $e->getMessage());
        }
    }
    
}
