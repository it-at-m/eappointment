<?php

namespace BO\Zmscalldisplay\Tests;

class InfoTest extends Base
{
    protected $classname = "Info";

    protected $arguments = [];

    protected $parameters = [];

    #[\Override]
    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'response' => $this->readFixture("GET_queue.json")
                ]
            ]
        );
        $response = $this->render([], [
            'collections' => [
                'scopelist' => '141'
            ]
        ], []);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('id="waitingClients"', (string) $response->getBody());
    }

    public function testSkipsRefreshWhenQueueRequestTimesOut()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readPostResult',
                    'url' => '/calldisplay/queue/',
                    'exception' => new \BO\Zmsclient\Psr7\RequestException(
                        'Operation timed out after 9002 milliseconds with 0 bytes received'
                    )
                ]
            ]
        );
        $response = $this->render([], [
            'collections' => [
                'scopelist' => '133,124'
            ]
        ], []);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }
}
