<?php

namespace BO\Zmsdldb\Transformers;

use Httpful\Request;

/**
 * Transform Munich's SADB export format to Berlin-compatible format
 */
class Munich
{
    const EXCLUSIVE_LOCATIONS = [
        // Standesamt München - registry office locations (exclusive, don't show alternatives)
        10470, 10351880, 10351882, 1064292, 10351883, 54260, 1061927, 
        10295168, 10469, 102365,
        // Standesamt München-Pasing
        10351880, 10351882, 10351883,
        // Sozialbürgerhaus locations
        103666, 103633, 101905,
    ];

    const LOCATION_PRIO_BY_DISPLAY_NAME = [
        'Bürgerbüro Ruppertstraße' => 100,
        'Bürgerbüro Orleansplatz' => 90,
        'Bürgerbüro Pasing' => 80,
        'Bürgerbüro Riesenfeldstraße' => 70,
        'Bürgerbüro Forstenrieder Allee' => 60,
        'Bürgerbüro Leonrodstraße' => 50,
        'Feuerwache 1 - Hauptfeuerwache im Zentrum' => 10,
        'Feuerwache 2 - Sendling' => 9,
        'Feuerwache 3 - Westend' => 8,
        'Feuerwache 4 - Schwabing' => 7,
        'Feuerwache 5 - Ramersdorf' => 6,
        'Feuerwache 6 - Pasing' => 5,
        'Feuerwache 7 - Milbertshofen' => 4,
        'Feuerwache 8 - Föhring' => 3,
        'Feuerwache 9 - Neuperlach' => 2,
        'Feuerwache 10 - Riem / Neue Messe' => 1
    ];

    const DONT_SHOW_LOCATION_BY_SERVICES = [
        [
            "locations" => [10489], // Bürgerbüro Ruppertstraße
            "services" => [1063453, 1063441, 1080582] // Reisepass, Personalausweis, Vorläufiger Reisepass
        ]
    ];

    const SERVICE_COMBINATIONS = [
        //BB
        [10295182],
        [10242339, 1063475, 1063441, 1063453, 10308996, 10224136, 10225205, 10225181, 1063426, 1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197, 10224132],
        [10225205, 1063441, 1063453, 10224132, 10242339, 10308996, 10224136, 10225181, 1063426, 1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197, 1063475],
        [10225181, 1063428, 1063426, 1063475, 10242339, 10308996, 10224136, 10225205,  10306925, 10225119, 1080843, 1063441, 1063453, 1076889, 1078273, 1080582, 10225197, 10224132],
        [1063426, 1063428, 1063475, 1063441, 10242339, 10308996, 10224136, 10225205, 10225181,  10306925, 10225119, 1080843, 1063453, 1076889, 1078273, 1080582, 10225197, 10224132],
        [10306925, 1063441, 1063453, 1063475, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197, 10224132],
        [1063428, 1063426, 10225181, 1063441, 10242339, 10308996, 10224136, 10225205, 10225189, 10306925, 10225119, 1080843, 1063453, 1076889, 1078273, 1080582, 10225197, 1063475, 10224132],
        [10225119, 1063475, 10242339, 1063453, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10306925, 1080843,  1063441, 1076889, 1078273, 1080582, 10225197,  10224132],
        [1063565, 10225129, 1064033, 10297413, 1063576],
        [10225129, 1063565, 1064033, 10297413, 1063576],
        [1064033, 1063565, 10225129, 10297413, 1063576],
        [1080843, 1063475, 10224132, 10308996, 10224136, 10242339, 10225205, 10225181, 1063426,  1063428, 10306925, 10225119,  1063441, 1063453, 1076889, 1078273, 1080582, 10225197, 1063486],
        [10297413, 1063565, 10225129, 1064033, 1063576],
        [1063576, 1063565, 10225129, 1064033, 10297413],
        [1063441, 10225205, 1063453, 1063475, 10242339, 10308996, 10224136,  10225181, 1063426,  1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197,  10224132],
        [10224136, 10242339, 10308996, 10225205, 10225181, 1063426, 1063428, 10306925, 10225119, 1080843,  1063441, 1063453, 1076889, 1078273, 1080582, 10225197,  1063475, 10224132],
        [1063453, 10225205, 1063441, 1063475, 10242339, 10308996, 10224136, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197,  10224132],
        [1076889, 1063441, 1063453, 1063475, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843, 1078273, 1080582, 10225197,  10224132],
        [1078273, 1063441, 1063453, 1076889, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843,  1080582, 10225197, 1063475, 10224132],
        [1080582, 1063441, 1063453, 1063475, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843,  1076889, 1078273, 10225197,  10224132],
        [10225197, 1063441, 1063453, 10225119, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426, 1063428, 10306925, 1080843, 1076889, 1078273, 1080582, 1063475, 10224132],
        [1063475, 1063441, 1063453, 10224132, 10242339, 10308996, 10224136, 10225205, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197, 1063486],
        [10224132,10225205, 1063441, 1063453, 10242339, 10308996, 10224136, 10225181, 1063426,  1063428, 10306925, 10225119, 1080843, 1076889, 1078273, 1080582, 10225197, 1063486, 1063475],
        //KFZ
        [1064076, 10392406, 10391604],
        [10392406, 1064076, 10391604],
        [10391604, 1064076, 10392406],
        [10115737, 1064268, 1064345, 1064374],
        [1064268, 10115737, 1064345, 1064374],
        [1064345, 10115737, 1064268, 1064374],
        [1064374, 10115737, 1064268, 1064345],
        [1064121, 1064354, 1064308, 10387573, 1064275, 1064130, 1063425, 1080502, 10387564, 1064271, 1064342, 1064333, 1071959, 1064311, 1064323, 1063424, 1064314, 10391602],
        [1063425, 1064121, 1064354, 1064308, 10387573, 1064275, 1064342, 1071959, 1064311, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602, 1080502],
        [1064342, 1064121, 1064354, 1064308, 10387573, 1064275, 1080502, 1064323, 1063425, 1071959, 1064311, 10387564, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602],
        [1064354, 1064121, 1064308, 10387573, 1064275, 1064314, 1063424, 1064333, 1064323, 1064130, 1064271, 1064342, 10391602, 1063425, 10387564, 1071959, 1064311, 1080502],
        [1064308, 1064121, 1064354, 10387573, 1064275, 1064130, 1064333, 1063425, 10391602, 10387564, 1064323, 1080502, 1064342, 1064271, 1071959, 1064311, 1063424, 1064314],
        [1071959, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1064342, 1064311, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602, 1080502],
        [1064311, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1064342, 1071959, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602, 1080502],
        [10387564, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1064342, 1071959, 1064311, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602, 1080502],
        [10387573, 1064121, 1064354, 1064308, 1064275, 1063425, 1064342, 1071959, 1064311, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602, 1080502],
        [1064323, 1064121, 1064354, 1064308, 10387573, 1064275, 1080502, 1063425, 10387564, 1064342, 1071959, 1064311, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602],
        [1064130, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1064333, 1080502, 1064323, 1064342, 1071959, 1064311, 10387564, 1063424, 1064314, 1064271, 10391602],
        [1064333, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1080502, 1064323, 1064342, 1071959, 1064311, 10387564, 1064130, 1063424, 1064314, 1064271, 10391602],
        [1063424, 1064121, 1064354, 1064308, 10387573, 1064275, 1064314, 1064130, 1064333, 1063425, 10391602, 1064271, 1080502, 1064323, 1064342, 1071959, 1064311, 10387564],
        [1064314, 1064121, 1064354, 1064308, 10387573, 1064275, 1064130, 1064333, 1063425, 1064323, 10391602, 1064342, 1064271, 1080502, 10387564, 1071959, 1064311, 1063424],
        [1064271, 1064121, 1064354, 1064308, 10387573, 1064275, 1063425, 1064342, 1071959, 1064311, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 10391602, 1080502],
        [1064275, 1064121, 1064354, 1064308, 10387573, 1064271, 1063425, 1064342, 1071959, 1064311, 10387564, 1064323, 1064130, 1064333, 1063424, 1064314, 10391602, 1080502],
        [10391602, 1064121, 1064354, 1064308, 10387573, 1064275, 1064323, 1064333, 10387564, 1063425, 1064342, 1071959, 1064311, 1064130, 1063424, 1064314, 1064271, 1080502],
        [1080502, 1064121, 1064354, 1064308, 10387573, 1064275, 10387564, 1063425, 1064342, 1071959, 1064311, 1064323, 1064130, 1064333, 1063424, 1064314, 1064271, 10391602],
        // Gewerbeamt
        [10300817, 10300814],
        [10300814, 10300817],
        [10300814, 10300808],
        [10300808, 10300814],
        // Fuehrerscheinstelle
        [1064361, 10383549],
        [10383549, 1064361],
    ];

    protected $publicUrl;
    protected $logger;

    public function __construct($publicUrl = '', $logger = null)
    {
        $this->publicUrl = $publicUrl ?: 'https://stadt.muenchen.de/en/buergerservice/terminvereinbarung.html/#';
        $this->logger = $logger;
    }

    /**
     * Fetch latest Munich SADB export and return the data
     */
    public function fetchLatestExport($indexUrl)
    {
        try {
            $response = Request::get($indexUrl)->send();
            $content = $response->raw_body;
            
            // Extract the latest export URL from the index page
            $urls = explode('https', $content);
            $latestUrl = 'https' . trim(end($urls));
            
            if ($this->logger) {
                $this->logger->info('Fetching Munich export', ['url' => $latestUrl]);
            }
            
            $exportResponse = Request::get($latestUrl)->send();
            return json_decode($exportResponse->raw_body, true);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to fetch Munich export', ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Transform Munich SADB format to Berlin-compatible services format
     */
    public function transformServices($data)
    {
        $mappedServices = [];

        foreach ($data['services'] ?? [] as $service) {
            $mappedService = [
                'id' => $service['id'],
                'name' => $service['name'],
                'description' => $service['description'] ?? '',
                'meta' => [
                    'lastupdate' => date('Y-m-d\TH:i:s'),
                    'url' => $this->publicUrl . "/services/{serviceId}",
                    'locale' => 'de',
                    'keywords' => implode(', ', $service['synonyms'] ?? []),
                    'translated' => true,
                    'hash' => '',
                    'id' => $service['id']
                ],
                'authorities' => [['id' => '1', 'name' => 'Stadtverwaltung München', 'webinfo' => 'https://muenchen.de']],
                'locations' => [],
                'leika' => $service['leikaId'] ?? null,
                'public' => true,
                'links' => [],
                'forms' => [],
                'appointment' => [
                    'link' => $this->publicUrl . "/services/{serviceId}"
                ],
                'maxQuantity' => 1,
                'duration' => 30, // Default duration
            ];

            // Get combinable services
            $combinableServices = $this->getServiceCombinations($service['id']);
            if ($combinableServices) {
                $mappedService['combinable'] = $combinableServices;
            }

            // Extract ZMS-specific fields
            foreach ($service['fields'] ?? [] as $field) {
                if (empty($field)) continue;

                if ($field['name'] === 'ZMS_MAX_ANZAHL') {
                    $mappedService['maxQuantity'] = $field['value'];
                }
                if ($field['name'] === 'ZMS_DAUER') {
                    $mappedService['duration'] = $field['value'];
                }
                if ($field['name'] === 'ZMS_INTERN') {
                    $mappedService['public'] = !$field['value'];
                }
                if ($field['name'] === 'GEBUEHRENRAHMEN') {
                    $mappedService['fees'] = $field['value'];
                }
                if ($field['name'] === 'FORMULARE_INFORMATIONEN') {
                    foreach ($field['values'] ?? [] as $form) {
                        $formData = ['name' => $form['label'], 'link' => $form['uri'], 'description' => false];
                        $mappedService['forms'][] = $formData;
                        $mappedService['links'][] = $formData;
                    }
                }
            }

            $mappedServices[] = $mappedService;
        }

        return [
            'data' => $mappedServices,
            'meta' => [
                'generated' => date('Y-m-d\TH:i:s'),
                'datacount' => count($mappedServices),
                'hash' => md5(json_encode($mappedServices))
            ]
        ];
    }

    /**
     * Transform Munich SADB format to Berlin-compatible locations format
     */
    public function transformLocations($data, $servicesData = null)
    {
        $mappedServices = [];
        if ($servicesData) {
            foreach ($servicesData['data'] ?? [] as $service) {
                $mappedServices[$service['id']] = $service;
            }
        }

        $mappedLocations = [];

        foreach ($data['locations'] ?? [] as $location) {
            if (!isset($location['altname2'])) continue;

            $name = $location['altname2'];
            $fullName = $name . (isset($location['altname1']) ? ' (' . $location['altname1'] . ')' : '');

            $mappedLocation = [
                'id' => $location['id'],
                'name' => $fullName,
                'displayName' => $name,
                'displayNameAlternatives' => $location['names'] ?? [],
                'organization' => $location['organisation'] ?? null,
                'organizationUnit' => $location['orgUnit'] ?? null,
                'public' => $location['public'] ?? true,
                'meta' => [
                    'url' => $this->publicUrl . "/locations/{locationId}",
                    'lastupdate' => date('Y-m-d\TH:i:s'),
                    'locale' => 'de',
                    'keywords' => implode(', ', $location['names'] ?? []),
                    'translated' => true,
                    'hash' => '',
                    'id' => $location['id']
                ],
                'address' => [
                    'house_number' => $location['address']['streetNumber'] ?? '',
                    'city' => $location['address']['city'] ?? 'München',
                    'postal_code' => $location['address']['zip'] ?? '',
                    'street' => $location['address']['street'] ?? '',
                    'hint' => false
                ],
                'geo' => isset($location['coordinate']) ? [
                    'lat' => $location['coordinate']['lat'],
                    'lon' => $location['coordinate']['lon']
                ] : null,
                'contact' => [
                    'email' => $location['email'] ?? '',
                    'fax' => $location['fax'] ?? '',
                    'phone' => $location['phone'] ?? '',
                    'signed_mail' => '0',
                    'signed_maillink' => '',
                    'webinfo' => '',
                    'competence' => ''
                ],
                'services' => []
            ];

            if (isset(self::LOCATION_PRIO_BY_DISPLAY_NAME[$name])) {
                $mappedLocation['prio'] = self::LOCATION_PRIO_BY_DISPLAY_NAME[$name];
            }

            // Check for exclusive locations (don't show alternatives)
            $mappedLocation['showAlternativeLocations'] = !in_array($mappedLocation['id'], self::EXCLUSIVE_LOCATIONS);

            // Check for service-based location hiding
            foreach (self::DONT_SHOW_LOCATION_BY_SERVICES as $avoidByServices) {
                if (in_array((int) $mappedLocation['id'], $avoidByServices['locations'])) {
                    $mappedLocation['dontShowByServices'] = $avoidByServices['services'];
                    break;
                }
            }

            // Map service references for this location
            $durationCommonDivisor = null;
            foreach ($location['extendedServiceReferences'] ?? [] as $reference) {
                if (!isset($mappedServices[$reference['refId']])) continue;

                $service = $mappedServices[$reference['refId']];
                $serviceRef = [
                    'service' => $reference['refId'],
                    'contact' => [],
                    'hint' => false,
                    'url' => str_replace(['{serviceId}', '{locationId}'], [$reference['refId'], $mappedLocation['id']], $this->publicUrl . "/services/{serviceId}/locations/{locationId}"),
                    'appointment' => [
                        'link' => str_replace(['{serviceId}', '{locationId}'], [$reference['refId'], $mappedLocation['id']], $this->publicUrl . "/services/{serviceId}/locations/{locationId}"),
                        'slots' => '1',
                        'external' => false,
                        'allowed' => true
                    ],
                    'onlineprocessing' => [
                        'description' => null,
                        'link' => str_replace('{serviceId}', $reference['refId'], $this->publicUrl . "/services/{serviceId}")
                    ],
                    'duration' => $mappedServices[$reference['refId']]['duration'] ?? 30
                ];

                if (isset($reference['public'])) {
                    $serviceRef['public'] = $reference['public'];
                }

                if (isset($reference['fields'])) {
                    foreach ($reference['fields'] as $field) {
                        if ($field['name'] === 'ZMS_DAUER') {
                            $serviceRef['duration'] = $field['value'];
                        }
                        if ($field['name'] === 'ZMS_MAX_ANZAHL') {
                            $serviceRef['maxQuantity'] = $field['value'];
                        }
                        if ($field['name'] === 'ZMS_INTERN') {
                            $serviceRef['public'] = !$field['value'];
                        }
                    }
                }

                // Calculate common divisor for slot times
                if ($durationCommonDivisor === null) {
                    $durationCommonDivisor = $serviceRef['duration'];
                } else {
                    $durationCommonDivisor = $this->getSlotTime($durationCommonDivisor, $serviceRef['duration']);
                }

                $mappedLocation['services'][] = $serviceRef;
            }

            // Set slot time for each service based on common divisor
            foreach ($mappedLocation['services'] as $key => $service) {
                if ($durationCommonDivisor && isset($service['duration'])) {
                    $mappedLocation['services'][$key]['appointment']['slots'] = (string) ((int)($service['duration'] / $durationCommonDivisor));
                }
            }

            // Set location-level slot properties
            $mappedLocation['slotTimeInMinutes'] = $durationCommonDivisor;
            $mappedLocation['forceSlotTimeUpdate'] = true;

            $mappedLocations[] = $mappedLocation;
        }

        return [
            'data' => $mappedLocations,
            'meta' => [
                'generated' => date('Y-m-d\TH:i:s'),
                'datacount' => count($mappedLocations),
                'hash' => md5(json_encode($mappedLocations))
            ]
        ];
    }

    /**
     * Get service combinations (services that can be booked together)
     */
    protected function getServiceCombinations($serviceId)
    {
        $serviceId = (int) $serviceId;

        foreach (self::SERVICE_COMBINATIONS as $combo) {
            if (empty($combo)) {
                continue;
            }

            if ((int) $combo[0] === $serviceId) {
                $list = array_merge([$serviceId], array_slice($combo, 1));
                $list = array_map('intval', $list);
                return array_values(array_unique($list, SORT_NUMERIC));
            }
        }

        return null;
    }

    /**
     * Calculate greatest common divisor for slot times
     */
    protected function getSlotTime($a, $b)
    {
        $slotTimes = [1, 2, 3, 4, 5, 6, 10, 12, 15, 20, 25, 30, 60];
        $slotTime = 1;

        foreach ($slotTimes as $time) {
            if ($a % $time === 0 && $b % $time === 0) {
                $slotTime = $time;
            }
        }

        return $slotTime;
    }
}

