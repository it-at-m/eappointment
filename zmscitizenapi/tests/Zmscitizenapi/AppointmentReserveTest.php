<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentReserveTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
