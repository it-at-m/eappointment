<?php

namespace BO\Zmscitizenapi\Tests;

abstract class ControllerTestCase extends \BO\Zmsclient\PhpUnit\Base
{
    protected $namespace = '\\BO\\Zmscitizenapi\\';

    public function readFixture(string $filename): string
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+\.json$/', $filename)) {
            throw new \InvalidArgumentException('Invalid fixture filename. Must be alphanumeric with .json extension');
        }
    
        $path = dirname(__FILE__) . '/fixtures/' . basename($filename);
    
        if (!is_readable($path) || !is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
    
        return file_get_contents($path);
    }

    public function renderJson(
        array  $arguments = [],
        array  $parameters = [],
        ?array $sessionData = null,
        string $method = 'GET',
        array  $assertStatusCodes = [ 200 ],
    ): array {
        $response = $this->render($arguments, $parameters, $sessionData, $method);
        $this->assertContainsEquals($response->getStatusCode(), $assertStatusCodes);
        return json_decode($response->getBody(), true);
    }
}
