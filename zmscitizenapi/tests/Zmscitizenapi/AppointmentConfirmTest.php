<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentConfirmTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
