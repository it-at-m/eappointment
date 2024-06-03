<?php

namespace BO\Zmscitizenapi\Tests;

class ScopesListTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}
