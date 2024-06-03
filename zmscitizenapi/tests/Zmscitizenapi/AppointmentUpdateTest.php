<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentUpdateTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson(method: 'POST');
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
