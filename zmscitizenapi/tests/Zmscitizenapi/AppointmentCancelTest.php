<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Localization\ErrorMessages;

class AppointmentCancelTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentCancel";

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