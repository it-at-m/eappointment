<?php

namespace BO\Zmscitizenapi\Tests;

class ScopeByIdGetTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
