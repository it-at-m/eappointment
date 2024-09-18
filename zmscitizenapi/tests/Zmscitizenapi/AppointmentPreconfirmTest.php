<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentPreconfirmTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentPreconfirm";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
