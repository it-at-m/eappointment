<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesByServiceListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
