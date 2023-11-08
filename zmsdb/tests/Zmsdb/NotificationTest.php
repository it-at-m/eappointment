<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Notification as Query;
use \BO\Zmsentities\Notification as Entity;

class NotificationTest extends Base
{
    public function testBasic()
    {
        $now = static::$now;
        $input = $this->getTestEntity();
        $input->process['status'] = 'pickup';

        $this->assertEquals('0', $input->getFirstClient()->emailSendCount);

        $query = new Query();
        $entity = $query->writeInQueue($input, $now);

        $this->assertEntity("\\BO\\Zmsentities\\Notification", $entity);
        $this->assertEquals("80410", $entity->getProcessId());
        $this->assertEquals("f22c", $entity->getProcessAuthKey());
        $this->assertEquals('1', $entity->getFirstClient()->notificationsSendCount);

        $collection = $query->readList(1);
        $this->assertTrue($collection->hasEntity($entity->id), "Missing Test Entity with ID 1234 in collection");

        $deleteTest = $query->deleteEntity($entity->id);
        $this->assertTrue($deleteTest, "Failed to delete Notification from Database.");

        $entity2 = $query->readEntity($entity->id);
        $this->assertFalse($entity2->hasId($entity->id), "Deleted Notification still exists in Database.");
    }

    public function testWriteInCalculationTable()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $input->process = new \BO\Zmsentities\Process($input->process);
        $this->assertTrue($query->writeInCalculationTable($input));
    }

    public function testWriteInCalculationTableWithoutProcess()
    {
        $query = new Query();
        $input = $this->getTestEntity();
        $input['process'] = new \BO\Zmsentities\Process(['id' => 141]);
        $this->assertFalse($query->writeInCalculationTable($input));
    }

    public function testExceptionWithoutTelephone()
    {
        $now = static::$now;
        $this->expectException('\BO\Zmsdb\Exception\Notification\ClientWithoutTelephone');
        $query = new Query();
        $input = $this->getTestEntity();
        $input->process['clients'][0]['telephone'] = '';
        $query->writeInQueue($input, $now);
    }

    public function testExceptionMissingProperty()
    {
        $now = static::$now;
        $this->expectException('\BO\Zmsentities\Exception\NotificationMissedProperty');
        $query = new Query();
        $input = $this->getTestEntity();
        unset($input->message);
        $query->writeInQueue($input, $now);
    }

    protected function getTestEntity()
    {
        $input = new Entity(array(
            "id" => 1234,
            "createIP" => "145.15.3.10",
            "createTimestamp" => 1447931596,
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
            "message" => "Denken Sie an ihren Termin mit der Nummer 80410",
            "process" => [
                "appointments"=>[
                    [
                        "date"=>"1464339600",
                        "scope"=>[
                            "id"=>"141"
                        ],
                        "slotCount"=>"1"
                    ]
                ],
                "clients" => [
                    [
                        "familyName" => "Max Mustermann",
                        "email" => "max@service.berlin.de",
                        "telephone" => "030 115"
                    ]
                ],
                "id" => 80410,
                "authKey" => "f22c",
                "reminderTimestamp" => 1447931730000,
                "scope" => [
                    "id" => 141
                ],
                "status" => "confirmed"
            ],
            "client" => [
                "familyName" => "Max Mustermann",
                "email" => "max@service.berlin.de",
                "telephone" => "030 115"
            ]

        ));
        return $input;
    }
}
