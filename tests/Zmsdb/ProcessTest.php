<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Status;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

class ProcessTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $input = $this->getTestProcessEntity();

        $process = $query->updateEntity($input);
        $process = $query->readEntity($process->id, $process->authKey);

        $this->assertEntity("\\BO\\Zmsentities\\Process", $process);
        $this->assertEquals(array('120686'), $process->getRequestIds());
        $this->assertEquals(151, $process->getScopeId());

        $process = $query->updateProcessStatus($process, 'confirmed');
        $this->assertEquals('confirmed', $process->getStatus());

        $deleteTest = $query->deleteEntity($process->id, $process->authKey);
        $this->assertTrue($deleteTest, "Failed to delete Process from Database.");

        $process = $query->readEntity($process->id, $process->authKey);
        $this->assertEquals('deleted', $process->getStatus());
    }

    public function testStatusFree()
    {
        $query = new Query();
        $now = new \DateTimeImmutable("2016-05-30 08:00");

        $calendar = $this->getTestCalendarEntity();
        $firstDay = $now->format('Y-m-d');
        $lastDay = date('Y-m-t', strtotime("+1 month", strtotime($firstDay)));
        $calendar->addFirstAndLastDay($firstDay, $lastDay);

        $processList = $query->readFreeProcesses($calendar, $now);
        $firstProcess = $processList->getFirstProcess();
        $this->assertTrue(
            $firstProcess->hasAppointment($now->format('U'), $firstProcess->getScopeId()),
            "Missing Appointment Date (". $firstDay .") in first free Process"
        );
    }

    protected function getTestCalendarEntity()
    {
        return (new Calendar())->getExample();
    }

    protected function getTestProcessEntity()
    {
         $input = new Entity(array(
            "amendment"=>"",
            "appointments"=>[
            [
                "date"=>"1464166800",
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
                        "confirmationEnabled"=>"0",
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
                        "lastGivenNumberTimestamp"=>"2016-03-24"
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
            "createTimestamp"=>"1459028767",
            "queue"=>[
            "arrivalTime"=>"00:00:00",
            "callCount"=>"0",
            "callTime"=>"00:00:00",
            "number"=>"0",
            "waitingTime"=>null,
            "reminderTimestamp"=>"0"
            ],
            "workstation"=>[
                "id"=>"0"
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
