<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentUpdateTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentUpdate";

    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
