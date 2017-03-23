<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

/**
 * @SuppressWarnings(TooManyPublicMethods)
 *
 */
class ProcessTest extends Base
{
    public function testReadEntityFailed()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\ProcessAuthFailed');

        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->readEntity($process->id, '1234');
    }

    public function testReadByQueueNumberAndScope()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = $query->writeNewFromTicketprinter($scope, $now);
        $process = $query->readByQueueNumberAndScope($process->queue['number'], $scope->id);
        $this->assertEquals(1, $process->queue['number']);
        $this->assertEquals(1459504500, $process->queue['arrivalTime']);
    }

    public function testExceptionAlreadyReserved()
    {
        $this->setExpectedException('\BO\Zmsdb\Exception\ProcessReserveFailed');

        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->writeEntityReserved($process, $now);
        $process = $query->writeEntityReserved($process, $now);
    }

    public function testExceptionSQLUpdateFailed()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $input->id = 1000;
        try {
            $query->writeEntityReserved($input, $now);
            $this->fail("Expected exception not thrown");
        } catch (\Exception $exception) {
            $this->assertContains('SQL UPDATE error on inserting new process', $exception->getMessage());
        }
    }

    public function testUpdateProcess()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process->amendment = 'Test amendment';
        $process = $query->updateEntity($process);

        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals('Test amendment', $process->amendment);
        $this->assertEquals(151, $process->getScopeId());

        $process = $query->updateProcessStatus($process, 'confirmed');
        $this->assertEquals('confirmed', $process->getStatus());
        $this->assertEquals(1464339600, $process->queue['arrivalTime']);
    }

    public function testProcessStatusCalled()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $input->queue['callTime'] = 1464350400;
        $process = $query->writeEntityReserved($input, $now);
        $process->amendment = 'Test amendment';
        $process = $query->updateEntity($process);

        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals('Test amendment', $process->amendment);
        $this->assertEquals(151, $process->getScopeId());

        $process = $query->updateProcessStatus($process, 'confirmed');
        $this->assertEquals('called', $process->getStatus());
    }

    public function testReadSlotCount()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->readSlotCount($process);
        $this->assertEquals(3, $process->getAppointments()->getFirst()['slotCount']);
    }

    public function testMultipleSlots()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $input->getFirstAppointment()->slotCount = 3;
        $process = $query->writeEntityReserved($input, $now);
        $process = $query->readEntity($process->id, $process->authKey);
        $this->assertEquals(3, $process->getFirstAppointment()->slotCount);
    }

    public function testDeleteProcess()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $deleteTest = $query->deleteEntity($process->id, $process->authKey);
        $this->assertTrue($deleteTest, "Failed to delete Process from Database.");

        $process = $query->readEntity($process->id, $process->authKey);
        $this->assertEquals('deleted', $process->getStatus());

        $process = $query->readEntity(); //check null
        $this->assertEquals(null, $process);
    }

    public function testReserveProcess()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $input = $this->getTestProcessEntity();
        $process = $query->writeEntityReserved($input, $now);
        $authCheck = $query->readAuthKeyByProcessId($process->id);
        $process = $query->readEntity($process->id, $authCheck['authKey']);
        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
    }

    public function testReadListByScopeAndTime()
    {
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $processList = $query->readProcessListByScopeAndTime(141, $now); //Heerstraße
        $this->assertEquals(102, $processList->count(), "Scope 141 Heerstraße should have 105 assigned processes");
    }

    public function testStatusFree()
    {
        $query = new Query();
        $now = new \DateTimeImmutable("2016-05-30 08:00");

        $calendar = $this->getTestCalendarEntity();
        $calendar->addFirstAndLastDay($now->getTimestamp(), 'Europe/Berlin');

        $processList = $query->readFreeProcesses($calendar, $now);
        $this->assertTrue(0 < count($processList));
    }

    public function testStatusReserved()
    {
        $query = new Query();
        $processList = $query->readReservedProcesses();
        $firstProcess = $processList->getFirstProcess();
        $process = $query->readEntity($firstProcess->id, $firstProcess->authKey);
        $this->assertEquals('reserved', $process->getStatus());
    }

    public function testSearch()
    {
        $query = new Query();
        $processList = $query->readSearch("J51362");
        $this->assertEntityList("\\BO\\Zmsentities\\Process", $processList);
        $this->assertEquals(6, $processList->count());
    }

    protected function getTestCalendarEntity()
    {
        return (new Calendar())->getExample();
    }

    /**
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    protected function getTestProcessEntity()
    {
        // https://localhost/terminvereinbarung/termin/time/1464339600/151/
        $input = new Entity(array(
            "amendment"=>"",
            "appointments"=>[
                [
                    "date"=>"1464339600",
                    "scope"=>[
                        "id"=>"151"
                    ],
                    "slotCount"=>"1"
                ]
            ],
            "scope"=>[
                "contact"=>[
                    "email"=>""
                ],
                "hint"=>"Bürgeramt MV ",
                "id"=>"151",
                "preferences"=>[
                    "appointment"=>[
                        "deallocationDuration"=>"5",
                        "endInDaysDefault"=>"60",
                        "multipleSlotsEnabled"=>"1",
                        "reservationDuration"=>"5",
                        "startInDaysDefault"=>"0"
                    ],
                    "client"=>[
                        "alternateAppointmentUrl"=>"",
                        "amendmentActivated"=>"0",
                        "amendmentLabel"=>"",
                        "emailRequired"=>"1",
                        "telephoneActivated"=>"1",
                        "telephoneRequired"=>"1"
                    ],
                    "notifications"=>[
                        "confirmationContent"=>"",
                        "enabled"=>"0",
                        "headsUpContent"=>"",
                        "headsUpTime"=>"0"
                    ],
                    "pickup"=>[
                        "alternateName"=>"Ausgabe",
                        "isDefault"=>"0"
                    ],
                    "queue"=>[
                        "callCountMax"=>"0",
                        "firstNumber"=>"1000",
                        "lastNumber"=>"1999",
                        "processingTimeAverage"=>"00:15:00",
                        "publishWaitingTimeEnabled"=>"1",
                        "statisticsEnabled"=>"1"
                    ],
                    "survey"=>[
                        "emailContent"=>"",
                        "enabled"=>"0",
                        "label"=>""
                    ],
                    "ticketprinter"=>[
                        "confirmationEnabled"=>"0",
                        "deactivatedText"=>"",
                        "notificationsAmendmentEnabled"=>"0",
                        "notificationsDelay"=>"0"
                    ],
                    "workstation"=>[
                        "emergencyEnabled"=>"0"
                    ]
                ],
                "shortName"=>"",
                "status"=>[
                    "emergency"=>[
                        "acceptedByWorkstation"=>"-1",
                        "activated"=>"0",
                        "calledByWorkstation"=>"-1"
                    ],
                    "queue"=>[
                        "ghostWorkstationCount"=>"-1",
                        "givenNumberCount"=>"11",
                        "lastGivenNumber"=>"1011",
                        "lastGivenNumberTimestamp"=>"1447925159"
                    ],
                    "ticketprinter"=>[
                        "deactivated"=>"1"
                    ]
                ],
                "department"=>[
                    "contact"=>[
                        "city"=>"Berlin",
                        "street"=>"Teichstr.",
                        "streetNumber"=>"1)",
                        "postalCode"=>"13407",
                        "region"=>"Berlin",
                        "country"=>"Germany",
                        "name"=>""
                    ],
                    "email"=>"buergeraemter@reinickendorf.berlin.de",
                    "id"=>"77",
                    "name"=>"Bürgeramt",
                    "preferences"=>[
                        "notifications"=>[
                            "enabled"=>null,
                            "identification"=>null,
                            "sendConfirmationEnabled"=>null,
                            "sendReminderEnabled"=>null
                        ]
                    ]
                ],
                "provider"=>[
                    "contact"=>[
                        "email"=>"buergeraemter@reinickendorf.berlin.de",
                        "city"=>"Berlin",
                        "country"=>"Germany",
                        "name"=>"Bürgeramt Märkisches Viertel",
                        "postalCode"=>"13435",
                        "region"=>"Berlin",
                        "street"=>"Wilhelmsruher Damm ",
                        "streetNumber"=>"142C"
                    ],
                    "source"=>"dldb",
                    "id"=>"122314",
                    "link"=>"https://service.berlin.de/standort/122314/",
                    "name"=>"Bürgeramt Märkisches Viertel"
                ]
            ],
            "clients"=>[
                [
                    "email"=>"max@service.berlin.de",
                    "emailSendCount"=>"1",
                    "familyName"=>"Max Mustermann",
                    "notificationsSendCount"=>"1",
                    "surveyAccepted"=>"0",
                    "telephone"=>"030 115"
                ]
            ],
            "createIP"=>"127.0.0.1",
            "createTimestamp" =>"1459028767",
            "queue"=>[
                "arrivalTime" =>"1464339600",
                "callCount" =>"0",
                "callTime" => "0",
                "lastCallTime" => "0",
                "number" =>"0",
                "waitingTime" => 60,
                "reminderTimestamp" =>"0"
            ],
            "requests"=>[
                [
                    "id"=>"120686",
                    "link"=>"https://service.berlin.de/dienstleistung/120686/",
                    "name"=>"Anmeldung einer Wohnung",
                    "source"=>"dldb"
                ]
            ],
            "status"=>"reserved"
        ));
        return $input;
    }
}
