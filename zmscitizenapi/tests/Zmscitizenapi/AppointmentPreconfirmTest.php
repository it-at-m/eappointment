<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentPreconfirmTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
