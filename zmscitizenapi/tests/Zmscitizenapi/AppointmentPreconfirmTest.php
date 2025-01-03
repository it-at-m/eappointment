<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class AppointmentPreconfirmTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentPreconfirm";

    public function setUp(): void
    {
        parent::setUp();
        
        \App::$source_name = 'unittest';

        if (\App::$cache) {
            \App::$cache->clear();
        }
    }

    public function testRendering() 
    {
        $responseData = $this->renderJson(
            method: 'POST',
            assertStatusCodes: [501]
        );
        $expectedError = ErrorMessages::get('notImplemented');
        $this->assertEquals($expectedError, $responseData);
    }
}
