<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\AppointmentDeallocateByCron;
use \BO\Zmsdb\Process as ProcessRepository;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsentities\Process as Entity;

class AppointmentDeallocateByCronTest extends Base
{

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 09:55');
        $helper = new AppointmentDeallocateByCron($now, false);
        $this->assertInstanceOf(AppointmentDeallocateByCron::class, $helper);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeallocateByCron($now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false);
        $this->assertEquals(2, $helper->getCount()['deallocated']);
    }

    public function testWithDeallocatedProcess()
    {
        $now = static::$now;
        $deallocateDate = clone $now->modify('- 6 minutes'); //entity scope with delete delay of 5 minutes
        $input = $this->getTestProcessEntity();
        $process = (new ProcessStatusFree())->writeEntityReserved($input, $now);
        $process = (new ProcessRepository())->writeCanceledEntity(
            $process->getId(),
            $process->getAuthKey(),
            $deallocateDate
        );
        $helper = new AppointmentDeallocateByCron($now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false);
        $this->assertEquals(3, $helper->getCount()['deallocated']);
        $processList = (new \BO\Zmsdb\Process)->readDeallocateProcessList($now, 10, 0);
        $this->assertEquals(
            'Abgesagter Termin gebucht am: 01.04.2016, 09:55 Uhr | ',
            $processList->getLast()->amendment
        );
        $this->assertEquals(3, $processList->count());
        $helper->startProcessing(true);
        $this->assertEquals(0, (new \BO\Zmsdb\Process)->readDeallocateProcessList($now, 10, 0)->count());
    }

    public function testWithoutDeallocatedProcess()
    {
        $now = static::$now;
        $deallocateDate = clone $now->modify('- 5 minutes'); //entity scope with delete delay of 5 minutes
        $input = $this->getTestProcessEntity();
        $process = (new ProcessStatusFree())->writeEntityReserved($input, $deallocateDate);
        $process = (new ProcessRepository())->writeCanceledEntity(
            $process->getId(),
            $process->getAuthKey(),
            $deallocateDate
        );

        $helper = new AppointmentDeallocateByCron($now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false);
        $this->assertEquals(2, $helper->getCount()['deallocated']);
        $this->assertEquals(2, (new \BO\Zmsdb\Process)->readDeallocateProcessList($now, 10, 0)->count());
    }

    /**
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    public function getTestProcessEntity()
    {
        // https://localhost/terminvereinbarung/termin/time/1464339600/151/
        $input = new Entity(array(
            "amendment"=>"",
            "appointments"=>[
                [
                    "date"=>"1464339600", // 2016-05-27 11:00:00
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
                        "processingTimeAverage"=>"15",
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
                "withAppointment" => 1,
                "arrivalTime" =>"1464339600",
                "callCount" =>"1459511700",
                "callTime" => "1459511700",
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
