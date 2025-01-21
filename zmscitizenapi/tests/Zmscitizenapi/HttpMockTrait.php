<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests;

trait HttpMockTrait 
{
    protected function setApiCalls(array $calls): void 
    {
        if (!isset(\App::$http)) {
            \App::$http = new class {
                private array $responses = [];
                
                public function setResponses(array $responses): void 
                {
                    $this->responses = $responses;
                }
                
                public function readGetResult(string $url, array $parameters = []): mixed 
                {
                    foreach ($this->responses as $response) {
                        if ($response['function'] === 'readGetResult' && 
                            $response['url'] === $url && 
                            $response['parameters'] === $parameters) {
                            if (isset($response['exception'])) {
                                throw $response['exception'];
                            }
                            return json_decode($response['response']);
                        }
                    }
                    return null;
                }

                public function readPostResult(string $url, mixed $data = null): mixed 
                {
                    foreach ($this->responses as $response) {
                        if ($response['function'] === 'readPostResult' && 
                            $response['url'] === $url) {
                            if (isset($response['exception'])) {
                                throw $response['exception'];
                            }
                            return json_decode($response['response']);
                        }
                    }
                    return null;
                }
            };
        }
        \App::$http->setResponses($calls);
    }

    protected function readFixture(string $filename): string 
    {
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.json$/', $filename)) {
            throw new \InvalidArgumentException('Invalid fixture filename');
        }
        
        $path = __DIR__ . '/fixtures/' . $filename;
        if (!is_readable($path)) {
            throw new \Exception('Fixture file not found: ' . $path);
        }
        
        return file_get_contents($path);
    }
}
