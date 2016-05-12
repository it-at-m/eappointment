<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Mail as Query;
use \BO\Zmsentities\Mail as Entity;

class MailTest extends Base
{
    public function testBasic()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $queueId = $query->writeInQueue($input);
        $entity = $query->readEntity($queueId);

        $this->assertEntity("\\BO\\Zmsentities\\Mail", $entity);
        $this->assertEquals("Das ist ein Plaintext Test", $entity->getPlainPart());
        $this->assertEquals("max@service.berlin.de", $entity->getFirstClient()['email']);

        $collection = $query->readList(1);
        $this->assertTrue($collection->hasEntity($queueId), "Missing Test Entity with ID 1234 in collection");

        $deleteTest = $query->deleteEntity($queueId);
        $this->assertTrue($deleteTest, "Failed to delete Mail from Database.");

        $entity = $query->readEntity($queueId);
        $this->assertFalse($entity->hasId($queueId), "Deleted Mail still exists in Database.");
    }

    protected function getTestEntity()
    {
        $input = new Entity(array(
            "id" => 1234,
            "createIP" => "145.15.3.10",
            "createTimestamp" => 1447931596000,
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
                    "mime" => "text/html",
                    "content" =>  "<h1>Title</h1><p>Message</p>",
                    "base64" => false
                ],
                [
                    "mime" => "text/plain",
                    "content" =>  "Das ist ein Plaintext Test",
                    "base64" => false
                ],
                [
                    "mime" => "text/calendar",
                    "content" =>  "Hier steht ein IcsString",
                    "base64" => false
                ]
            ),
            "process" => [
                "clients" => array(
                    [
                        "familyName" => "Max Mustermann",
                        "email" => "max@service.berlin.de",
                        "telephone" => "030 115"
                    ]
                ),
                "id" => 80410,
                "authKey" => "f22c",
                "reminderTimestamp" => 1447931730000,
                "scope" => [
                    "id" => 151,
                    "preferences" => [
                        "appointment" => [
                            "deallocationDuration" => "5",
                            "endInDaysDefault" => "60",
                            "multipleSlotsEnabled" => "1",
                            "reservationDuration" => "5",
                            "startInDaysDefault" => "0"
                        ],
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
                            "confirmationEnabled" => "0",
                            "enabled" => "1",
                            "headsUpContent" => "",
                            "headsUpTime" => "0"
                        ],
                        "pickup" => [
                            "alternateName" => "Ausgabe",
                            "isDefault" => "0"
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
                            "deactivatedText" => "",
                            "notificationsAmendmentEnabled" => "0",
                            "notificationsDelay" => "0"
                        ],
                        "workstation" => [
                            "emergencyEnabled" => "0"
                        ]
                    ],
                ],
                "status" => "confirmed"
            ],
            "subject" => "Example Mail"
        ));
        return $input;
    }
}
