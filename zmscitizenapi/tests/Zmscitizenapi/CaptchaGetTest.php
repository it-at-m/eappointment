<?php

namespace BO\Zmscitizenapi\Tests;

class CaptchaGetTest extends Base
{
    public function testRendering() {
        $responseData = $this->renderJson();
        $this->assertEqualsCanonicalizing([], $responseData);
    }
}