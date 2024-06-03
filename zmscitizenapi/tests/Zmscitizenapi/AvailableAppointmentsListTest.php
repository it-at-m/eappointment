<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableAppointmentsListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
