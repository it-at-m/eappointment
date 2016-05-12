<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Notification as Query;
use \BO\Zmsentities\Notification as Entity;

class NotificationTest extends Base
{
    public function testBasic()
    {
        $input = $this->getTestEntity();
        $query = new Query();
        $queueId = $query->writeInQueue($input);
        $entity = $query->readEntity($queueId, 1);

        $this->assertEntity("\\BO\\Zmsentities\\Notification", $entity);
        $this->assertEquals("80410", $entity->getProcessId());
        $this->assertEquals("f22c", $entity->getProcessAuthKey());

        $collection = $query->readList(1);
        $this->assertTrue($collection->hasEntity($queueId), "Missing Test Entity with ID 1234 in collection");

        $deleteTest = $query->deleteEntity($queueId);
        $this->assertTrue($deleteTest, "Failed to delete Notification from Database.");

        $entity = $query->readEntity($queueId);
        $this->assertFalse($entity->hasId($queueId), "Deleted Notification still exists in Database.");
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
            "message" => "Denken Sie an ihren Termin mit der Nummer 80410",
            "process" => [
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
            ]
        ));
        return $input;
    }
}
