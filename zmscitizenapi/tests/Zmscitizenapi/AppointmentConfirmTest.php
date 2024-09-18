<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentConfirmTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentConfirm";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
