<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesServicesRelationsTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
