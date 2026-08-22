<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests;

use BO\Zmscitizenbackend\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testModuleNameIsCitizenBackend(): void
    {
        $this->assertSame('zmscitizenbackend', Application::MODULE_NAME);
        $this->assertSame('zms', Application::IDENTIFIER);
    }
}
