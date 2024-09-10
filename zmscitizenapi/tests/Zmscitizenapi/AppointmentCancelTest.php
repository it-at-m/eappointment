<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentCancelTest extends Base
{

    protected $classname = "AppointmentCancel";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
