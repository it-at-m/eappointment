<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentCancelTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentCancel";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
