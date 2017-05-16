<?php

namespace BO\Zmsapi\Tests;

class MailAddTest extends Base
{
    protected $classname = "MailAdd";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], [
            '__body' => '{
              "createIP": "145.15.3.10",
              "createTimestamp": 1447931596000,
              "department": {
                "id": 123
              },
              "multipart": [
                {
                  "queueId": "1234",
                  "mime": "text/html",
                  "content": "<h1>Title</h1><p>Message</p>",
                  "base64": false
                },
                {
                  "queueId": "1234",
                  "mime": "text/plain",
                  "content": "Title\nMessage",
                  "base64": false
                }
              ],
              "process": {
                "clients": [
                  {
                    "familyName": "Max Mustermann",
                    "email": "max@service.berlin.de",
                    "telephone": "030 115"
                  }
                ],
                "id": 123456,
                "authKey": "1234",
                "reminderTimestamp": 1447931730000,
                "scope": {
                  "id": 151
                },
                "status": "confirmed"
              },
              "subject": "Example Mail",
              "client": [
                  {
                      "familyName": "Max Mustermann",
                      "email": "max@service.berlin.de"
                  }
              ]
            }'
        ], []);
        $this->assertContains('mail.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
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
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $this->expectExceptionCode(400);
        $this->render([], [
            '__body' => '{
              "createIP": "145.15.3.10",
              "createTimestamp": 1447931596000,
              "multipart": [
                {
                  "queueId": "1234",
                  "mime": "text/html",
                  "content": "<h1>Title</h1><p>Message</p>",
                  "base64": false
                },
                {
                  "queueId": "1234",
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
