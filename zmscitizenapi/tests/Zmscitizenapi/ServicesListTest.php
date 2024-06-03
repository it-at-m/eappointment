<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
