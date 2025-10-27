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
            ];

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
                    ]
                ];

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

                $mappedLocation['services'][] = $serviceRef;
            }

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
}

