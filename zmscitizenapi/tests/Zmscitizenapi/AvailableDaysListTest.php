<?php

namespace BO\Zmscitizenapi\Tests;

class AvailableDaysListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
