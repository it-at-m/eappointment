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

    /**
     * @param array{status?: string, email?: string|null, familyName?: string|null, externalUserId?: string|null} $overrides
     */
    protected function processFixtureWith(array $overrides, string $baseFixture = 'GET_process.json'): string
    {
        $data = json_decode($this->readFixture($baseFixture), true, 512, JSON_THROW_ON_ERROR);
        if (isset($overrides['status'])) {
            $data['data']['status'] = $overrides['status'];
            $data['data']['queue']['status'] = $overrides['status'];
        }
        if (array_key_exists('email', $overrides)) {
            $data['data']['clients'][0]['email'] = $overrides['email'];
        }
        if (array_key_exists('familyName', $overrides)) {
            $data['data']['clients'][0]['familyName'] = $overrides['familyName'];
        }
        if (array_key_exists('externalUserId', $overrides)) {
            $data['data']['externalUserId'] = $overrides['externalUserId'];
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
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
