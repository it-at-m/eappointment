<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentConfirmTest extends Base
{
    protected $classname = "AppointmentConfirm";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
