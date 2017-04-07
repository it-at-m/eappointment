<?php
// @codingStandardsIgnoreFile

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Mail as Query;
use \BO\Zmsentities\Mail as Entity;

class MailTest extends Base
{
    public function testBasic()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $entity = $query->writeInQueue($input);

        $this->assertEntity("\\BO\\Zmsentities\\Mail", $entity);
        $this->assertEquals('Das ist ein Plaintext Test', $entity->getPlainPart());
        $this->assertEquals("Max Mustermann", $entity->getFirstClient()['familyName']);

        $collection = $query->readList(2);
        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Mail", $collection);
        $this->assertEquals(true, $collection->hasEntity('1234')); //Test Entity exists

        $entityId = $entity->id;
        $deleteTest = $query->deleteEntity($entityId);
        $this->assertTrue($deleteTest, "Failed to delete Mail from Database.");

        $entity = $query->readEntity($entityId);
        $this->assertFalse($entity->hasId($entityId), "Deleted Mail still exists in Database.");
    }

    public function testWriteMailInQueueFailed()
    {
        $this->setExpectedException('BO\Zmsdb\Exception\MailWriteInQueueFailed');
        $query = new Query();
        $input = $this->getTestEntity();
        $input->process['clients'][0]['email'] = null;
        $entity = $query->writeInQueue($input);
        $query->deleteEntity($entity->id);
    }

    public function testWriteMimepartFailed()
    {
        $this->setExpectedException('BO\Zmsdb\Exception\MailWritePartFailed');
        $query = new Query();
        $input = $this->getTestEntity();
        $input->multipart[0]['content'] = null;
        $entity = $query->writeInQueue($input);
    }

    public function testDeleteByProcessId()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $entity = $query->writeInQueue($input);
        $deleteTest = $query->deleteEntityByProcess($input->process['id']);
        $entity = $query->readEntity($entity->id);
        $this->assertFalse($entity->hasId(), "Delete Mail by process id failed.");
    }

    protected function getTestEntity()
    {
        return new Entity(array(
            "id" => 1234,
            "createIP" => "145.15.3.10",
            "createTimestamp" => 1447931596000,
            "client" => [
                "email" => "test@service.berlinonline.de",
                "familyName" => "Test Entity",
                ],
            "department" => [
                "id" => 72,
                "preferences" => [
                    "notifications" => [
                        "enabled" => true,
                        "identification" => "terminvereinbarung@mitte.berlin.de",
                        "sendConfirmationEnabled" => true,
                        "sendReminderEnabled" => true
                    ]
                ]
            ],
            "multipart" => array(
                [
                    "queueId" => "1234",
                    "mime" => "text/html",
                    "content" =>  "<h1>Title</h1><p>Message</p>",
                    "base64" => false
                ],
                [
                    "queueId" => "1234",
                    "mime" => "text/plain",
                    "content" =>  "Das ist ein Plaintext Test",
                    "base64" => false
                ],
                [
                    "queueId" => "1234",
                    "mime" => "text/calendar",
                    "content" =>  "QkVHSU46VkNBTEVOREFSDQpWRVJTSU9OOjIuMA0KUFJPRElEOmh0dHA6Ly93d3cuZXhhbXBsZS5jb20vY2FsZW5kYXJhcHBsaWNhdGlvbi8NCk1FVEhPRDpQVUJMSVNIDQpCRUdJTjpWRVZFTlQNClVJRDptYXhAc2VydmljZS5iZXJsaW4uZGUNCk9SR0FOSVpFUjtDTj0iTWF4eCBNdXN0ZXJtYW5uLCBFeGFtcGxlIEluYy4iOk1BSUxUTzptYXgubXVzdGVybWFubkBtdXN0ZXJtYWlsLmRlDQpMT0NBVElPTjpTb21ld2hlcmUNClNVTU1BUlk6RWluZSBLdXJ6aW5mbw0KREVTQ1JJUFRJT046QmVzY2hyZWlidW5nIGRlcyBUZXJtaW5lcw0KQ0xBU1M6UFVCTElDDQpEVFNUQVJUOjIwMTYwOTEwVDIyMDAwMFoNCkRURU5EOjIwMTYwOTE5VDIxNTkwMFoNCkRUU1RBTVA6MjAxNjA4MTJUMTI1OTAwWg0KRU5EOlZFVkVOVA0KRU5EOlZDQUxFTkRBUg==",
                    "base64" => true
                ]
            ),
            "process" => [
                "clients" => array(
                    [
                        "familyName" => "Max Mustermann",
                        "email" => "torsten.kunst@berlinonline.de",
                        "telephone" => "030 115"
                    ]
                ),
                "appointments"=>[
                    [
                        "date"=>"1464339600",
                        "scope"=>[
                            "id"=>"151"
                        ],
                        "slotCount"=>"1"
                    ]
                ],
                "id" => 80410,
                "authKey" => "f22c",
                "reminderTimestamp" => 1447931730000,
                "scope" => [
                    "id" => 151,
                    "preferences" => [
                        "client" => [
                            "alternateAppointmentUrl" => "",
                            "amendmentActivated" => "0",
                            "amendmentLabel" => "",
                            "emailRequired" => "1",
                            "telephoneActivated" => "1",
                            "telephoneRequired" => "1"
                        ],
                        "notifications" => [
                            "confirmationContent" => "",
                            "enabled" => "1",
                            "headsUpContent" => "",
                            "headsUpTime" => "0"
                        ],
                        "queue" => [
                            "callCountMax" => "0",
                            "callDisplayText" => "Herzlich Willkommen \r\nin Berlin Reinickendorf\r\n=====================\r\nTIP => Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter =>\r\nhttp =>//www.berlin.de/ba-reinickendorf/org/buergeramt/",
                            "firstNumber" => "1000",
                            "lastNumber" => "1999",
                            "processingTimeAverage" => "00 =>15 =>00",
                            "publishWaitingTimeEnabled" => "1",
                            "statisticsEnabled" => "1"
                        ],
                        "survey" => [
                            "emailContent" => "",
                            "enabled" => "0",
                            "label" => ""
                        ],
                        "ticketprinter" => [
                            "confirmationEnabled" => "0",
                            "deactivatedText" => "",
                            "notificationsAmendmentEnabled" => "0",
                            "notificationsDelay" => "0"
                        ],
                    ],
                ],
                "status" => "confirmed"
            ],
            "subject" => "Example Mail"
        ));
    }
}
