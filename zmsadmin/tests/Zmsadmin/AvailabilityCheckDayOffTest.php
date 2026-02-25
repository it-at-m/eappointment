<?php

namespace BO\Zmsadmin\Tests;

class AvailabilityCheckDayOffTest extends Base
{
    protected $arguments = [];

    protected $classname = "\BO\Zmsadmin\Helper\AvailabilityCheckDayOff";

    public function testRendering()
    {
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "$schema": "https://schema.berlin.de/queuemanagement/availability.json",
                        "id": "81871",
                        "weekday": {
                            "sunday": "0",
                            "monday": "2",
                            "tuesday": "0",
                            "wednesday": "0",
                            "thursday": "0",
                            "friday": "0",
                            "saturday": "0"
                        },
                        "repeat": {
                            "afterWeeks": "1",
                            "weekOfMonth": "0"
                        },
                        "bookable": {
                            "startInDays": "0",
                            "endInDays": "60"
                        },
                        "workstationCount": {
                            "public": "3",
                            "intern": "3"
                        },
                        "lastChange": 1566566532,
                        "multipleSlotsAllowed": "0",
                        "slotTimeInMinutes": "10",
                        "startDate": 1452553200,
                        "endDate": 1463868000,
                        "startTime": "08:00:00",
                        "endTime": "15:50:00",
                        "type": "appointment",
                        "description": "",
                        "scope": {
                            "id": "141",
                            "source": "dldb",
                            "contact": {
                                "name": "Bürgeramt Heerstraße",
                                "street": "Heerstr. 12, 14052 Berlin",
                                "email": "",
                                "country": "Germany"
                            },
                            "provider": {
                                "id": "122217",
                                "source": "dldb",
                                "contact": {
                                    "city": "Berlin",
                                    "country": "Germany",
                                    "name": "Bürgeramt Heerstraße",
                                    "postalCode": "14052",
                                    "region": "Berlin",
                                    "street": "Heerstr.",
                                    "streetNumber": "12"
                                },
                                "link": "https://service.berlin.de/standort/122217/",
                                "name": "Bürgeramt Heerstraße"
                            },
                            "$schema": "https://schema.berlin.de/queuemanagement/scope.json",
                            "hint": "Nr. wird zum Termin aufgerufen",
                            "lastChange": 1566566542,
                            "shortName": "",
                            "preferences": {
                                "appointment": {
                                    "deallocationDuration": "10",
                                    "infoForAppointment": "",
                                    "infoForAllAppointments": "",
                                    "endInDaysDefault": "60",
                                    "multipleSlotsEnabled": "0",
                                    "reservationDuration": "20",
                                    "startInDaysDefault": "0",
                                    "notificationConfirmationEnabled": "1",
                                    "notificationHeadsUpEnabled": "1"
                                },
                                "client": {
                                    "amendmentActivated": "0",
                                    "amendmentLabel": "",
                                    "emailFrom": "test@example.com",
                                    "emailRequired": "0",
                                    "telephoneActivated": "0",
                                    "telephoneRequired": 0
                                },
                                "notifications": {
                                    "confirmationContent": "",
                                    "headsUpContent": "Ihre Wartezeit beträgt noch ca. 30 Min., bitte informieren Sie sich über die Aufrufanzeige im Bürgeramt, in welchem Raum Sie erwartet werden. Wartenr:",
                                    "headsUpTime": "30"
                                },
                                "queue": {
                                    "callCountMax": "0",
                                    "callDisplayText": "Herzlich Willkommen \r\nin Charlottenburg-Wilmersdorf\r\n=====================\r\nTIP: Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter:\r\nhttp://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html",
                                    "firstNumber": "1",
                                    "lastNumber": "499",
                                    "maxNumberContingent": "0",
                                    "processingTimeAverage": "15",
                                    "statisticsEnabled": "1"
                                },
                                "survey": {
                                    "emailContent": "Text E-Mail-Kundenbefragung",
                                    "enabled": "0",
                                    "label": ""
                                },
                                "ticketprinter": {
                                    "buttonName": "Bürgeramt",
                                    "confirmationEnabled": "0",
                                    "deactivatedText": "",
                                    "notificationsAmendmentEnabled": "0",
                                    "notificationsEnabled": "1",
                                    "notificationsDelay": "0"
                                },
                                "workstation": {
                                    "emergencyEnabled": "1",
                                    "emergencyRefreshInterval": "5"
                                }
                            },
                            "dayoff": []
                        }
                    }
                ]
            }',
        ], [], 'POST');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '"overridesDayOff":false',
            (string) $response->getBody()
        );
    }

    public function testRenderingWithOverridesDayOff()
    {
        $response = $this->render([], [
            '__body' => '{
                "availabilityList": [
                    {
                        "$schema": "https://schema.berlin.de/queuemanagement/availability.json",
                        "id": "1",
                        "weekday": {
                            "sunday": "0",
                            "monday": "0",
                            "tuesday": "0",
                            "wednesday": "0",
                            "thursday": "0",
                            "friday": "1",
                            "saturday": "0"
                        },
                        "repeat": {
                            "afterWeeks": "1",
                            "weekOfMonth": "0"
                        },
                        "bookable": {
                            "startInDays": "0",
                            "endInDays": "60"
                        },
                        "workstationCount": {
                            "public": "1",
                            "intern": "1"
                        },
                        "lastChange": 1566566532,
                        "multipleSlotsAllowed": "0",
                        "slotTimeInMinutes": "10",
                        "startDate": 1458860400,
                        "endDate": 1458860400,
                        "startTime": "08:00:00",
                        "endTime": "12:00:00",
                        "type": "appointment",
                        "description": "",
                        "scope": {
                            "dayoff": [
                                {
                                    "id": "302",
                                    "date": 1458860400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                }
                            ]
                        }
                    }
                ]
            }',
        ], [], 'POST');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '"overridesDayOff":true',
            (string) $response->getBody()
        );
    }
}

