<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesByOfficeListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
