<?php

namespace BO\Zmsadmin\Tests;

class AvailabilityConflictsTest extends Base
{
    protected $arguments = [];

    protected $classname = "AvailabilityConflicts";

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
                            "callcenter": "3",
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
                                    "endInDaysDefault": "60",
                                    "multipleSlotsEnabled": "0",
                                    "reservationDuration": "20",
                                    "startInDaysDefault": "0",
                                    "notificationConfirmationEnabled": "1",
                                    "notificationHeadsUpEnabled": "1"
                                },
                                "client": {
                                    "alternateAppointmentUrl": "",
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
                                "logs": {
                                    "type": "object",
                                    "additionalProperties": false,
                                    "properties": {
                                        "deleteLogsOlderThanDays": {
                                            "type": "string",
                                            "description": "Number of days after which is log deleted",
                                            "default": ""
                                        }
                                    }
                                },
                                "pickup": {
                                    "alternateName": "Ausgabe",
                                    "isDefault": "0"
                                },
                                "queue": {
                                    "callCountMax": "0",
                                    "callDisplayText": "Herzlich Willkommen \r\nin Charlottenburg-Wilmersdorf\r\n=====================\r\nTIP: Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter:\r\nhttp://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html",
                                    "firstNumber": "1",
                                    "lastNumber": "499",
                                    "maxNumberContingent": "0",
                                    "processingTimeAverage": "15",
                                    "publishWaitingTimeEnabled": "1",
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
                            "status": {
                                "emergency": {
                                    "acceptedByWorkstation": "-1",
                                    "activated": "0",
                                    "calledByWorkstation": "-1"
                                },
                                "queue": {
                                    "ghostWorkstationCount": "-1",
                                    "givenNumberCount": "46",
                                    "lastGivenNumber": "47",
                                    "lastGivenNumberTimestamp": 1458774000
                                },
                                "ticketprinter": {
                                    "deactivated": "0"
                                }
                            },
                            "dayoff": [
                                {
                                    "id": "302",
                                    "date": 1458860400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "303",
                                    "date": 1459116000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "304",
                                    "date": 1462053600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "305",
                                    "date": 1462399200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "306",
                                    "date": 1463349600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "307",
                                    "date": 1475445600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "308",
                                    "date": 1482620400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "309",
                                    "date": 1482706800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "310",
                                    "date": 1483225200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "311",
                                    "date": 1492120800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "312",
                                    "date": 1492380000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "313",
                                    "date": 1493589600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "314",
                                    "date": 1495663200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "315",
                                    "date": 1496613600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "316",
                                    "date": 1506981600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "317",
                                    "date": 1514156400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "318",
                                    "date": 1514242800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "319",
                                    "date": 1514761200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "320",
                                    "date": 1522360800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "321",
                                    "date": 1522620000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "322",
                                    "date": 1525125600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "323",
                                    "date": 1525903200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "324",
                                    "date": 1526853600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "325",
                                    "date": 1538517600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "326",
                                    "date": 1545692400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "327",
                                    "date": 1545778800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "328",
                                    "date": 1546297200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "329",
                                    "date": 1555624800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "330",
                                    "date": 1555884000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "331",
                                    "date": 1556661600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "332",
                                    "date": 1559167200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "333",
                                    "date": 1560117600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "334",
                                    "date": 1570053600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "335",
                                    "date": 1577228400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "336",
                                    "date": 1577314800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "337",
                                    "date": 1577833200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "338",
                                    "date": 1586469600,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "339",
                                    "date": 1586728800,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "340",
                                    "date": 1588284000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "341",
                                    "date": 1590012000,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "342",
                                    "date": 1590962400,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "343",
                                    "date": 1601676000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "344",
                                    "date": 1608850800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "345",
                                    "date": 1608937200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "346",
                                    "date": 1609455600,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "347",
                                    "date": 1617314400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "348",
                                    "date": 1617573600,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "349",
                                    "date": 1619820000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "350",
                                    "date": 1620856800,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "351",
                                    "date": 1621807200,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "352",
                                    "date": 1633212000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "353",
                                    "date": 1640386800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "354",
                                    "date": 1640473200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "1570",
                                    "date": 1478041200,
                                    "lastChange": 1566566540,
                                    "name": "Personalversammlung"
                                }
                            ]
                        }
                    },
                    {
                        "$schema": "https://schema.berlin.de/queuemanagement/availability.json",
                        "id": 0,
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
                            "public": 0,
                            "callcenter": 0,
                            "intern": 0
                        },
                        "lastChange": 1566566532,
                        "multipleSlotsAllowed": "0",
                        "slotTimeInMinutes": "10",
                        "startDate": 1452553200,
                        "endDate": 1463868000,
                        "startTime": "08:00:00",
                        "endTime": "16:00:00",
                        "type": "openinghours",
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
                                    "endInDaysDefault": "60",
                                    "multipleSlotsEnabled": "0",
                                    "reservationDuration": "20",
                                    "startInDaysDefault": "0",
                                    "notificationConfirmationEnabled": "1",
                                    "notificationHeadsUpEnabled": "1"
                                },
                                "client": {
                                    "alternateAppointmentUrl": "",
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
                                "logs": {
                                    "type": "object",
                                    "additionalProperties": false,
                                    "properties": {
                                        "deleteLogsOlderThanDays": {
                                            "type": "string",
                                            "description": "Number of days after which is log deleted",
                                            "default": ""
                                        }
                                    }
                                },
                                "pickup": {
                                    "alternateName": "Ausgabe",
                                    "isDefault": "0"
                                },
                                "queue": {
                                    "callCountMax": "0",
                                    "callDisplayText": "Herzlich Willkommen \r\nin Charlottenburg-Wilmersdorf\r\n=====================\r\nTIP: Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter:\r\nhttp://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html",
                                    "firstNumber": "1",
                                    "lastNumber": "499",
                                    "maxNumberContingent": "0",
                                    "processingTimeAverage": "15",
                                    "publishWaitingTimeEnabled": "1",
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
                            "status": {
                                "emergency": {
                                    "acceptedByWorkstation": "-1",
                                    "activated": "0",
                                    "calledByWorkstation": "-1"
                                },
                                "queue": {
                                    "ghostWorkstationCount": "-1",
                                    "givenNumberCount": "46",
                                    "lastGivenNumber": "47",
                                    "lastGivenNumberTimestamp": 1458774000
                                },
                                "ticketprinter": {
                                    "deactivated": "0"
                                }
                            },
                            "dayoff": [
                                {
                                    "id": "302",
                                    "date": 1458860400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "303",
                                    "date": 1459116000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "304",
                                    "date": 1462053600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "305",
                                    "date": 1462399200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "306",
                                    "date": 1463349600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "307",
                                    "date": 1475445600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "308",
                                    "date": 1482620400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "309",
                                    "date": 1482706800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "310",
                                    "date": 1483225200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "311",
                                    "date": 1492120800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "312",
                                    "date": 1492380000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "313",
                                    "date": 1493589600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "314",
                                    "date": 1495663200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "315",
                                    "date": 1496613600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "316",
                                    "date": 1506981600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "317",
                                    "date": 1514156400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "318",
                                    "date": 1514242800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "319",
                                    "date": 1514761200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "320",
                                    "date": 1522360800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "321",
                                    "date": 1522620000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "322",
                                    "date": 1525125600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "323",
                                    "date": 1525903200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "324",
                                    "date": 1526853600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "325",
                                    "date": 1538517600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "326",
                                    "date": 1545692400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "327",
                                    "date": 1545778800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "328",
                                    "date": 1546297200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "329",
                                    "date": 1555624800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "330",
                                    "date": 1555884000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "331",
                                    "date": 1556661600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "332",
                                    "date": 1559167200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "333",
                                    "date": 1560117600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "334",
                                    "date": 1570053600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "335",
                                    "date": 1577228400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "336",
                                    "date": 1577314800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "337",
                                    "date": 1577833200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "338",
                                    "date": 1586469600,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "339",
                                    "date": 1586728800,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "340",
                                    "date": 1588284000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "341",
                                    "date": 1590012000,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "342",
                                    "date": 1590962400,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "343",
                                    "date": 1601676000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "344",
                                    "date": 1608850800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "345",
                                    "date": 1608937200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "346",
                                    "date": 1609455600,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "347",
                                    "date": 1617314400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "348",
                                    "date": 1617573600,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "349",
                                    "date": 1619820000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "350",
                                    "date": 1620856800,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "351",
                                    "date": 1621807200,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "352",
                                    "date": 1633212000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "353",
                                    "date": 1640386800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "354",
                                    "date": 1640473200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "1570",
                                    "date": 1478041200,
                                    "lastChange": 1566566540,
                                    "name": "Personalversammlung"
                                }
                            ]
                        },
                        "tempId": "spontaneous_ID_1"
                    },
                    {
                        "$schema": "https://schema.berlin.de/queuemanagement/availability.json",
                        "id": null,
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
                            "callcenter": "3",
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
                        "description": "Kopie von 2016-01-12 - 2016-05-22",
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
                                    "endInDaysDefault": "60",
                                    "multipleSlotsEnabled": "0",
                                    "reservationDuration": "20",
                                    "startInDaysDefault": "0",
                                    "notificationConfirmationEnabled": "1",
                                    "notificationHeadsUpEnabled": "1"
                                },
                                "client": {
                                    "alternateAppointmentUrl": "",
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
                                "logs": {
                                    "type": "object",
                                    "additionalProperties": false,
                                    "properties": {
                                        "deleteLogsOlderThanDays": {
                                            "type": "string",
                                            "description": "Number of days after which is log deleted",
                                            "default": ""
                                        }
                                    }
                                },
                                "pickup": {
                                    "alternateName": "Ausgabe",
                                    "isDefault": "0"
                                },
                                "queue": {
                                    "callCountMax": "0",
                                    "callDisplayText": "Herzlich Willkommen \r\nin Charlottenburg-Wilmersdorf\r\n=====================\r\nTIP: Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter:\r\nhttp://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html",
                                    "firstNumber": "1",
                                    "lastNumber": "499",
                                    "maxNumberContingent": "0",
                                    "processingTimeAverage": "15",
                                    "publishWaitingTimeEnabled": "1",
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
                            "status": {
                                "emergency": {
                                    "acceptedByWorkstation": "-1",
                                    "activated": "0",
                                    "calledByWorkstation": "-1"
                                },
                                "queue": {
                                    "ghostWorkstationCount": "-1",
                                    "givenNumberCount": "46",
                                    "lastGivenNumber": "47",
                                    "lastGivenNumberTimestamp": 1458774000
                                },
                                "ticketprinter": {
                                    "deactivated": "0"
                                }
                            },
                            "dayoff": [
                                {
                                    "id": "302",
                                    "date": 1458860400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "303",
                                    "date": 1459116000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "304",
                                    "date": 1462053600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "305",
                                    "date": 1462399200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "306",
                                    "date": 1463349600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "307",
                                    "date": 1475445600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "308",
                                    "date": 1482620400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "309",
                                    "date": 1482706800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "310",
                                    "date": 1483225200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "311",
                                    "date": 1492120800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "312",
                                    "date": 1492380000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "313",
                                    "date": 1493589600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "314",
                                    "date": 1495663200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "315",
                                    "date": 1496613600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "316",
                                    "date": 1506981600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "317",
                                    "date": 1514156400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "318",
                                    "date": 1514242800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "319",
                                    "date": 1514761200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "320",
                                    "date": 1522360800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "321",
                                    "date": 1522620000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "322",
                                    "date": 1525125600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "323",
                                    "date": 1525903200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "324",
                                    "date": 1526853600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "325",
                                    "date": 1538517600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "326",
                                    "date": 1545692400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "327",
                                    "date": 1545778800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "328",
                                    "date": 1546297200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "329",
                                    "date": 1555624800,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "330",
                                    "date": 1555884000,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "331",
                                    "date": 1556661600,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "332",
                                    "date": 1559167200,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "333",
                                    "date": 1560117600,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "334",
                                    "date": 1570053600,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "335",
                                    "date": 1577228400,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "336",
                                    "date": 1577314800,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "337",
                                    "date": 1577833200,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "338",
                                    "date": 1586469600,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "339",
                                    "date": 1586728800,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "340",
                                    "date": 1588284000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "341",
                                    "date": 1590012000,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "342",
                                    "date": 1590962400,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "343",
                                    "date": 1601676000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "344",
                                    "date": 1608850800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "345",
                                    "date": 1608937200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "346",
                                    "date": 1609455600,
                                    "lastChange": 1566566540,
                                    "name": "Neujahr"
                                },
                                {
                                    "id": "347",
                                    "date": 1617314400,
                                    "lastChange": 1566566540,
                                    "name": "Karfreitag"
                                },
                                {
                                    "id": "348",
                                    "date": 1617573600,
                                    "lastChange": 1566566540,
                                    "name": "Ostermontag"
                                },
                                {
                                    "id": "349",
                                    "date": 1619820000,
                                    "lastChange": 1566566540,
                                    "name": "Maifeiertag"
                                },
                                {
                                    "id": "350",
                                    "date": 1620856800,
                                    "lastChange": 1566566540,
                                    "name": "Christi Himmelfahrt"
                                },
                                {
                                    "id": "351",
                                    "date": 1621807200,
                                    "lastChange": 1566566540,
                                    "name": "Pfingstmontag"
                                },
                                {
                                    "id": "352",
                                    "date": 1633212000,
                                    "lastChange": 1566566540,
                                    "name": "Tag der Deutschen Einheit"
                                },
                                {
                                    "id": "353",
                                    "date": 1640386800,
                                    "lastChange": 1566566540,
                                    "name": "1. Weihnachtstag"
                                },
                                {
                                    "id": "354",
                                    "date": 1640473200,
                                    "lastChange": 1566566540,
                                    "name": "2. Weihnachtstag"
                                },
                                {
                                    "id": "1570",
                                    "date": 1478041200,
                                    "lastChange": 1566566540,
                                    "name": "Personalversammlung"
                                }
                            ]
                        },
                        "tempId": "__temp__0"
                    }
                ],
                "selectedDate": "2016-04-04",
                "selectedAvailability": {
                    "$schema": "https://schema.berlin.de/queuemanagement/availability.json",
                    "id": null,
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
                        "callcenter": "3",
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
                    "description": "Kopie von 2016-01-12 - 2016-05-22",
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
                                "endInDaysDefault": "60",
                                "multipleSlotsEnabled": "0",
                                "reservationDuration": "20",
                                "startInDaysDefault": "0",
                                "notificationConfirmationEnabled": "1",
                                "notificationHeadsUpEnabled": "1"
                            },
                            "client": {
                                "alternateAppointmentUrl": "",
                                "amendmentActivated": "0",
                                "amendmentLabel": "",
                                "emailFrom": "test@example.com",
                                "emailRequired": "0",
                                "telephoneActivated": "0",
                                "telephoneRequired": 0
                            },
                            "logs": {
                                "type": "object",
                                "additionalProperties": false,
                                "properties": {
                                    "deleteLogsOlderThanDays": {
                                        "type": "string",
                                        "description": "Number of days after which is log deleted",
                                        "default": ""
                                    }
                                }
                            },
                            "notifications": {
                                "confirmationContent": "",
                                "headsUpContent": "Ihre Wartezeit beträgt noch ca. 30 Min., bitte informieren Sie sich über die Aufrufanzeige im Bürgeramt, in welchem Raum Sie erwartet werden. Wartenr:",
                                "headsUpTime": "30"
                            },
                            "pickup": {
                                "alternateName": "Ausgabe",
                                "isDefault": "0"
                            },
                            "queue": {
                                "callCountMax": "0",
                                "callDisplayText": "Herzlich Willkommen \r\nin Charlottenburg-Wilmersdorf\r\n=====================\r\nTIP: Termin statt Wartezeit!\r\n=====================\r\nNutzen Sie die Online Terminvergabe unter:\r\nhttp://www.berlin.de/ba-charlottenburg-wilmersdorf/org/buergerdienste/buergeraemter.html",
                                "firstNumber": "1",
                                "lastNumber": "499",
                                "maxNumberContingent": "0",
                                "processingTimeAverage": "15",
                                "publishWaitingTimeEnabled": "1",
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
                        "status": {
                            "emergency": {
                                "acceptedByWorkstation": "-1",
                                "activated": "0",
                                "calledByWorkstation": "-1"
                            },
                            "queue": {
                                "ghostWorkstationCount": "-1",
                                "givenNumberCount": "46",
                                "lastGivenNumber": "47",
                                "lastGivenNumberTimestamp": 1458774000
                            },
                            "ticketprinter": {
                                "deactivated": "0"
                            }
                        },
                        "dayoff": [
                            {
                                "id": "302",
                                "date": 1458860400,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "303",
                                "date": 1459116000,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "304",
                                "date": 1462053600,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "305",
                                "date": 1462399200,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "306",
                                "date": 1463349600,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "307",
                                "date": 1475445600,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "308",
                                "date": 1482620400,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "309",
                                "date": 1482706800,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "310",
                                "date": 1483225200,
                                "lastChange": 1566566540,
                                "name": "Neujahr"
                            },
                            {
                                "id": "311",
                                "date": 1492120800,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "312",
                                "date": 1492380000,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "313",
                                "date": 1493589600,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "314",
                                "date": 1495663200,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "315",
                                "date": 1496613600,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "316",
                                "date": 1506981600,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "317",
                                "date": 1514156400,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "318",
                                "date": 1514242800,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "319",
                                "date": 1514761200,
                                "lastChange": 1566566540,
                                "name": "Neujahr"
                            },
                            {
                                "id": "320",
                                "date": 1522360800,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "321",
                                "date": 1522620000,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "322",
                                "date": 1525125600,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "323",
                                "date": 1525903200,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "324",
                                "date": 1526853600,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "325",
                                "date": 1538517600,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "326",
                                "date": 1545692400,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "327",
                                "date": 1545778800,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "328",
                                "date": 1546297200,
                                "lastChange": 1566566540,
                                "name": "Neujahr"
                            },
                            {
                                "id": "329",
                                "date": 1555624800,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "330",
                                "date": 1555884000,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "331",
                                "date": 1556661600,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "332",
                                "date": 1559167200,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "333",
                                "date": 1560117600,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "334",
                                "date": 1570053600,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "335",
                                "date": 1577228400,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "336",
                                "date": 1577314800,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "337",
                                "date": 1577833200,
                                "lastChange": 1566566540,
                                "name": "Neujahr"
                            },
                            {
                                "id": "338",
                                "date": 1586469600,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "339",
                                "date": 1586728800,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "340",
                                "date": 1588284000,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "341",
                                "date": 1590012000,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "342",
                                "date": 1590962400,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "343",
                                "date": 1601676000,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "344",
                                "date": 1608850800,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "345",
                                "date": 1608937200,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "346",
                                "date": 1609455600,
                                "lastChange": 1566566540,
                                "name": "Neujahr"
                            },
                            {
                                "id": "347",
                                "date": 1617314400,
                                "lastChange": 1566566540,
                                "name": "Karfreitag"
                            },
                            {
                                "id": "348",
                                "date": 1617573600,
                                "lastChange": 1566566540,
                                "name": "Ostermontag"
                            },
                            {
                                "id": "349",
                                "date": 1619820000,
                                "lastChange": 1566566540,
                                "name": "Maifeiertag"
                            },
                            {
                                "id": "350",
                                "date": 1620856800,
                                "lastChange": 1566566540,
                                "name": "Christi Himmelfahrt"
                            },
                            {
                                "id": "351",
                                "date": 1621807200,
                                "lastChange": 1566566540,
                                "name": "Pfingstmontag"
                            },
                            {
                                "id": "352",
                                "date": 1633212000,
                                "lastChange": 1566566540,
                                "name": "Tag der Deutschen Einheit"
                            },
                            {
                                "id": "353",
                                "date": 1640386800,
                                "lastChange": 1566566540,
                                "name": "1. Weihnachtstag"
                            },
                            {
                                "id": "354",
                                "date": 1640473200,
                                "lastChange": 1566566540,
                                "name": "2. Weihnachtstag"
                            },
                            {
                                "id": "1570",
                                "date": 1478041200,
                                "lastChange": 1566566540,
                                "name": "Personalversammlung"
                            }
                        ]
                    },
                    "tempId": "__temp__0"
                }
            }'
        ], [], 'POST');
        $this->assertStringContainsString('Zwei \u00d6ffnungszeiten sind gleich', (string)$response->getBody());
        $this->assertStringContainsString('2016-04-04', (string)$response->getBody());
        $this->assertStringContainsString('2016-04-11', (string)$response->getBody());
        $this->assertStringContainsString('2016-04-18', (string)$response->getBody());
        $this->assertStringContainsString('2016-04-25', (string)$response->getBody());
        $this->assertStringContainsString('2016-05-02', (string)$response->getBody());
        $this->assertStringContainsString('2016-05-09', (string)$response->getBody());
        $this->assertStringNotContainsString('2016-05-16', (string)$response->getBody());
        $this->assertStringContainsString('"conflictIdList":["81871","__temp__0"]', (string)$response->getBody());
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
