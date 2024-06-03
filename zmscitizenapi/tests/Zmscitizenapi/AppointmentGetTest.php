<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentGetTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
