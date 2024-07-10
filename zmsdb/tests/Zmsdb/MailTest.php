<?php
// @codingStandardsIgnoreFile

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Mail as Query;
use \BO\Zmsentities\Mail as Entity;

class MailTest extends Base
{
    public function testBasic()
    {
        $now = static::$now;
        $input = $this->getTestEntity();
        $query = new Query();
        $query->writeInQueue($input, $now);
        $query->writeInQueue($input, $now->modify('+ 5 Minutes'));

        $collection = $query->readList(2, 2);
        $entity = $collection->getFirst();
        $this->assertEntity("\\BO\\Zmsentities\\Mail", $entity);
        $this->assertEquals('Das ist ein Plaintext Test', $entity->getPlainPart());
        $this->assertEquals("D54643264", $entity->getFirstClient()['familyName']);

        $firstIn = $collection->getFirst()->createTimestamp;
        $secondIn = $collection->getLast()->createTimestamp;
        $this->assertTrue($firstIn < $secondIn);

        $collection = $query->readList(2, 2, 'DESC');
        $firstIn = $collection->getFirst()->createTimestamp;
        $secondIn = $collection->getLast()->createTimestamp;
        $this->assertFalse($firstIn < $secondIn);

        $collection->addEntity($input);
        $this->assertEntityList("\\BO\\Zmsentities\\Mail", $collection);
        $this->assertEquals(true, $collection->hasEntity('1234')); //Test Entity exists

        $entityId = $entity->id;
        $deleteTest = $query->deleteEntity($entityId);
        $this->assertTrue($deleteTest, "Failed to delete Mail from Database.");

        $entity = $query->readEntity($entityId);
        $this->assertFalse($entity->hasId($entityId), "Deleted Mail still exists in Database.");
    }

    public function testWriteInQueueWithAdmin()
    {
        $input = $this->getTestEntity();
        $input->multipart = array(
            [
                "queueId" => "1234",
                "mime" => "text/html",
                "content" =>  "<h1>Title</h1><p>Die Terminänderung wurde initiiert via admin</p>",
                "base64" => false
            ],
            [
                "queueId" => "1234",
                "mime" => "text/plain",
                "content" =>  "Die Terminänderung wurde initiiert via admin",
                "base64" => false
            ]
        );
        $entity = (new Query)->writeInQueueWithAdmin($input);
        $this->assertStringContainsString('Die Terminänderung wurde initiiert via', $entity->getPlainPart());
    }

    public function testWriteInQueueWithDailyProcessList()
    {
        $now = static::$now;
        $scope = (new \BO\Zmsdb\Scope)->readEntity(451, 1); // Mobiles Bürgeramt Reinickendorf with Admin Email
        $processList = new \BO\Zmsentities\Collection\ProcessList();
        $processList->addEntity(\BO\Zmsentities\Process::createExample());
        $mail = (new \BO\Zmsentities\Mail())->toScopeAdminProcessList($processList, $scope, $now);

        $entity = (new Query)->writeInQueueWithDailyProcessList($scope, $mail);
        $this->assertStringContainsString('Termine am 2016-04-01 (1 gesamt)', $entity->getHtmlPart());
        $this->assertStringContainsString('Max Mustermann', $entity->getHtmlPart());
        $this->assertStringContainsString('18:52', $entity->getHtmlPart());
    }

    public function testWriteInQueueWithPickupStatus()
    {
        $now = static::$now;
        $entity = $this->getTestEntity();
        $entity->process['status'] = 'pickup';
        $this->assertEquals('0', $entity->getFirstClient()->emailSendCount);
        $entity = (new Query)->writeInQueue($entity, $now);
        $this->assertEntity("\\BO\\Zmsentities\\Mail", $entity);
        $this->assertEquals('1', $entity->getFirstClient()->emailSendCount);
    }

    public function testExceptionWithoutMail()
    {
        $this->expectException('\BO\Zmsdb\Exception\Mail\ClientWithoutEmail');
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity();
        $input->process['clients'][0]['email'] = '';
        $query->writeInQueue($input, $now);
    }

    public function testWriteMimepartFailed()
    {
        $this->expectException('BO\Zmsdb\Exception\MailWritePartFailed');
        $now = static::$now;
        $query = new Query();
        $input = $this->getTestEntity();
        $input->multipart[0]['content'] = null;
        $query->writeInQueue($input, $now);
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
                        "familyName" => "D54643264",
                        "email" => "zms@service.berlinonline.de",
                        "telephone" => "004917680588471"
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
                            "customTextfieldActivated" => "0",
                            "appointmentsPerMail" => "",
                            "slotsPerAppointment" => "",
                            "whitelistedMails" => "",
                            "customTextfieldLabel" => "",
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
                    "contact" => [
                        "name" => "admin",
                        "email" => "zms@service.berlinonline.de"
                    ]
                ],
                "status" => "confirmed"
            ],
            "subject" => "Example Mail"
        ));
    }
}
