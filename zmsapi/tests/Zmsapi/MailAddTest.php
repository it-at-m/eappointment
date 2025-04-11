<?php

namespace BO\Zmsapi\Tests;

class MailAddTest extends Base
{
    protected $classname = "MailAdd";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => $this->readFixture('GetMail.json')
        ], []);
        $this->assertStringContainsString('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
        return $response;
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testUnvalidInput()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsdb\Exception\Mail\ClientWithoutEmail');
        $this->expectExceptionCode(404);
        $this->render([], [
            '__body' => '{
              "createIP": "145.15.3.10",
              "createTimestamp": 1447931596000,
              "multipart": [
                {
                  "queueId": 1234,
                  "mime": "text/html",
                  "content": "<h1>Title</h1><p>Message</p>",
                  "base64": false
                },
                {
                  "queueId": 1234,
                  "mime": "text/plain",
                  "content": "Title\nMessage",
                  "base64": false
                }
              ],
              "subject": "Example Mail"
            }'
        ], []);
    }
}
