<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentPreconfirmTest extends Base
{

    protected $classname = "AppointmentPreconfirm";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
