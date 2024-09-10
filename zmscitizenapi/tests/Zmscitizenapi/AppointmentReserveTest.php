<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentReserveTest extends Base
{

    protected $classname = "AppointmentReserve";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
