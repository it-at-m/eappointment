<?php

namespace BO\Zmsdldb\Transformers;

use Httpful\Request;
use Psr\Log\LoggerInterface;

/**
 * Transform Munich's SADB export format to Berlin-compatible format
 */
class Munich
{
    const EXCLUSIVE_LOCATIONS = [
        //SZE
        54285,
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
        ],
        [
            "locations" => [10286848, 10286849, 10181770, 10204387, 10204388, 10227989, 1060068], // Bürgerbüros ohne Abholung Personalausweis, Reisepass oder eID-Karte
            "services" => [10295182] // Abholung Personalausweis, Reisepass oder eID-Karte - only available at Ruppertstraße (10492)
        ]
    ];

    /**
     * Offices where disabledByServices/DONT_SHOW_LOCATION_BY_SERVICES are interpreted with special
     * "exclusive vs mixed" semantics. Grouped so JumpIn with one office auto-selects the
     * equivalent in the same group (e.g. 10489 ⟷ 10502). Mirrors dldb-mapper/app/map.php.
     */
    const LOCATIONS_ALLOW_DISABLED_MIX = [
        [10489, 10502],
    ];

    /** Aligned with dldb-mapper/app/map.php DONT_SHOW_SERVICE_ON_START_PAGE */
    const DONT_SHOW_SERVICE_ON_START_PAGE = [
        10396802,
        1063648,
        1063731,
        1071907,
        10502137,
        10314100,
        10416410,
        10323113,
        1080716
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

    public function __construct(string $publicUrl = '', ?LoggerInterface $logger = null)
    {
        if ($publicUrl && !filter_var($publicUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid public URL provided');
        }
        $this->publicUrl = $publicUrl ?: 'https://stadt.muenchen.de/en/buergerservice/terminvereinbarung.html/#';
        $this->logger = $logger;
    }

    /**
     * Apply corporate proxy to Httpful request when HTTPS_PROXY / HTTP_PROXY is set.
     * Cron and CI often do not inherit .bashrc; sourcing in cronjob.hourly helps, this is a second safeguard.
     */
    private function requestWithOptionalProxy(string $url): Request
    {
        $req = Request::get($url);
        $proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy')
            ?: getenv('HTTP_PROXY') ?: getenv('http_proxy');
        if ($proxy !== false && $proxy !== '') {
            $parts = parse_url($proxy);
            if (!empty($parts['host'])) {
                $port = isset($parts['port']) ? (int) $parts['port'] : 80;
                $req->useProxy($parts['host'], $port);
            }
        }
        return $req;
    }

    /**
     * Fetch latest Munich SADB export and return the data
     */
    public function fetchLatestExport(string $indexUrl): array
    {
        try {
            $response = $this->requestWithOptionalProxy($indexUrl)->timeout(45)->send();
            if ((int)($response->code ?? 0) !== 200) {
                throw new \RuntimeException("Index fetch failed with status {$response->code}");
            }
            $content = $response->raw_body;

            // Extract JSON export URLs using regex and pick the last one (latest)
            if (!preg_match_all('#https://[^"\'\'\s<>]+\\.json#i', $content, $matches) || empty($matches[0])) {
                throw new \RuntimeException('No JSON export links found on index page');
            }
            $latestUrl = end($matches[0]);

            $this->logger?->info('Fetching Munich export', ['url' => $latestUrl]);

            $exportResponse = $this->requestWithOptionalProxy($latestUrl)->timeout(120)->send();
            if ((int)($exportResponse->code ?? 0) !== 200) {
                throw new \RuntimeException("Export fetch failed with status {$exportResponse->code}");
            }

            $body = $exportResponse->raw_body;
            try {
                return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $snippet = preg_replace('/\s+/', ' ', substr((string) $body, 0, 400));
                throw new \RuntimeException(
                    'Export JSON parse failed (' . $e->getMessage() . '). '
                    . 'Often means the HTTP response was not JSON (e.g. block page or missing HTTPS_PROXY in cron). '
                    . 'Body preview: ' . $snippet,
                    0,
                    $e
                );
            }
        } catch (\Throwable $e) {
            $this->logger?->error('Failed to fetch Munich export', ['error' => $e->getMessage(), 'url' => $indexUrl]);
            throw $e;
        }
    }

    /**
     * Path to bundled SADB overwrite (ex-dldb-mapper prod.json). Passkalender 10502 + Pass services.
     */
    public static function defaultSadbOverwritePath(): string
    {
        return dirname(__DIR__, 3) . '/resources/munich_sadb_overwrite.json';
    }

    /**
     * Merge overwrite JSON into raw SADB export — same role as dldb-mapper mapImport($overwrite).
     * - Services: merge by id, fields merged by name (overwrite wins).
     * - Locations: merge by id (extendedServiceReferences by refId) or append if missing (e.g. 10502).
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function applySadbOverwrite(array $data, ?string $overwritePath = null): array
    {
        $path = $overwritePath ?: self::defaultSadbOverwritePath();
        if (!is_readable($path)) {
            $this->logger?->warning('Munich SADB overwrite not readable, skipping merge', ['path' => $path]);
            return $data;
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            return $data;
        }
        try {
            $additional = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger?->error('Munich SADB overwrite JSON invalid', ['path' => $path, 'error' => $e->getMessage()]);
            return $data;
        }

        $data['services'] = $data['services'] ?? [];
        $data['locations'] = $data['locations'] ?? [];

        foreach ($additional['services'] ?? [] as $svc) {
            $id = $svc['id'] ?? null;
            if ($id === null) {
                continue;
            }
            foreach ($data['services'] as $k => $existing) {
                if ((string)($existing['id'] ?? '') !== (string)$id) {
                    continue;
                }
                $origFields = $existing['fields'] ?? [];
                $newFields = $svc['fields'] ?? [];
                $data['services'][$k] = array_merge($existing, $svc);
                $data['services'][$k]['fields'] = $this->mergeListByKey($origFields, $newFields, 'name');
                break;
            }
        }

        foreach ($additional['locations'] ?? [] as $ov) {
            $oid = (string)($ov['id'] ?? '');
            if ($oid === '') {
                continue;
            }
            $idx = null;
            foreach ($data['locations'] as $k => $loc) {
                if ((string)($loc['id'] ?? '') === $oid) {
                    $idx = $k;
                    break;
                }
            }
            if ($idx === null) {
                $data['locations'][] = $ov;
                $this->logger?->info('Munich SADB overwrite: appended location', ['id' => $oid]);
                continue;
            }
            $existing = $data['locations'][$idx];
            $origRefs = $existing['extendedServiceReferences'] ?? [];
            $newRefs = $ov['extendedServiceReferences'] ?? [];
            $data['locations'][$idx] = array_merge($existing, $ov);
            $data['locations'][$idx]['extendedServiceReferences'] = $this->mergeListByKey($origRefs, $newRefs, 'refId');
            $this->logger?->info('Munich SADB overwrite: merged location', ['id' => $oid]);
        }

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $originalList
     * @param array<int, array<string, mixed>> $additionalList
     * @return array<int, array<string, mixed>>
     */
    private function mergeListByKey(array $originalList, array $additionalList, string $key): array
    {
        $by = [];
        foreach ($originalList as $el) {
            if (isset($el[$key])) {
                $by[(string)$el[$key]] = $el;
            }
        }
        foreach ($additionalList as $el) {
            if (isset($el[$key])) {
                $by[(string)$el[$key]] = $el;
            }
        }
        return array_values($by);
    }

    /**
     * Transform Munich SADB format to Berlin-compatible services format
     */
    public function transformServices(array $data): array
    {
        $timestamp = date('Y-m-d\TH:i:s');
        $mappedServices = [];

        foreach ($data['services'] ?? [] as $service) {
            $mappedService = [
                'id' => $service['id'],
                'name' => $service['name'],
                'description' => $service['description'] ?? '',
                'meta' => [
                    'lastupdate' => $timestamp,
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
                'showOnStartPage' => true,
                'duration' => 30, // Default duration
            ];

            // Get combinable services
            $combinableServices = $this->getServiceCombinations($service['id']);
            if ($combinableServices) {
                $mappedService['combinable'] = $combinableServices;
            }

            $mappedService['showOnStartPage'] = !in_array($service['id'], self::DONT_SHOW_SERVICE_ON_START_PAGE);

            // Extract ZMS-specific fields
            foreach ($service['fields'] ?? [] as $field) {
                if (empty($field)) {
                    continue;
                }

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
                'generated' => $timestamp,
                'datacount' => count($mappedServices),
                'hash' => md5(json_encode($mappedServices))
            ]
        ];
    }

    /**
     * Transform Munich SADB format to Berlin-compatible locations format.
     * When $servicesData is provided, also returns services with each service's
     * `locations` filled (same shape as dldb-mapper mapImport services output).
     *
     * @return array{locations: array, services: ?array{data: array, meta: array}}
     */
    public function transformLocations(array $data, ?array $servicesData = null): array
    {
        $mappedServices = $this->indexServicesByIds($servicesData);
        $mappedLocations = [];

        foreach ($data['locations'] ?? [] as $location) {
            $processed = $this->processLocation($location, $mappedServices);
            if ($processed) {
                $mappedLocations[] = $processed;
            }
        }

        $mergedServicesPayload = null;
        if ($servicesData !== null) {
            $servicesList = [];
            foreach ($servicesData['data'] ?? [] as $service) {
                $id = $service['id'];
                if (isset($mappedServices[$id])) {
                    $servicesList[] = $mappedServices[$id];
                }
            }
            $timestamp = date('Y-m-d\TH:i:s');
            $mergedServicesPayload = [
                'data' => $servicesList,
                'meta' => [
                    'generated' => $timestamp,
                    'datacount' => count($servicesList),
                    'hash' => md5(json_encode($servicesList)),
                ],
            ];
        }

        return [
            'locations' => $this->buildLocationResponse($mappedLocations),
            'services' => $mergedServicesPayload,
        ];
    }

    protected function indexServicesByIds(?array $servicesData): array
    {
        $mappedServices = [];
        if ($servicesData) {
            foreach ($servicesData['data'] ?? [] as $service) {
                $mappedServices[$service['id']] = $service;
            }
        }
        return $mappedServices;
    }

    private function processLocation(array $location, array &$mappedServices): ?array
    {
        if (!isset($location['altname2'])) {
            return null;
        }

        $mappedLocation = $this->buildLocationMetadata($location);

        $this->applyLocationRules($mappedLocation, $mappedLocation['displayName']);

        $durationCommonDivisor = $this->processServiceReferences($location, $mappedLocation, $mappedServices);

        $this->calculateSlotTimes($mappedLocation, $durationCommonDivisor);

        return $mappedLocation;
    }

    private function buildLocationMetadata(array $location): array
    {
        $name = $location['altname2'];
        $fullName = $name . (isset($location['altname1']) ? ' (' . $location['altname1'] . ')' : '');

        return [
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
    }

    private function applyLocationRules(array &$mappedLocation, string $displayName): void
    {
        if (isset(self::LOCATION_PRIO_BY_DISPLAY_NAME[$displayName])) {
            $mappedLocation['prio'] = self::LOCATION_PRIO_BY_DISPLAY_NAME[$displayName];
        }

        $mappedLocation['showAlternativeLocations'] = !in_array($mappedLocation['id'], self::EXCLUSIVE_LOCATIONS);

        foreach (self::DONT_SHOW_LOCATION_BY_SERVICES as $avoidByServices) {
            if (in_array((int) $mappedLocation['id'], $avoidByServices['locations'])) {
                $mappedLocation['dontShowByServices'] = $avoidByServices['services'];
                break;
            }
        }

        // Mark locations that participate in the "exclusive vs mixed" disabled-services logic.
        // Output group IDs so JumpIn with one office can auto-select the equivalent.
        foreach (self::LOCATIONS_ALLOW_DISABLED_MIX as $group) {
            if (in_array((int) $mappedLocation['id'], $group, true)) {
                $mappedLocation['allowDisabledServicesMix'] = $group;
                break;
            }
        }
    }

    private function processServiceReferences(array $location, array &$mappedLocation, array &$mappedServices): ?int
    {
        $durationCommonDivisor = null;

        foreach ($location['extendedServiceReferences'] ?? [] as $reference) {
            if (!isset($mappedServices[$reference['refId']])) {
                continue;
            }

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

            $locationRef = [
                'location' => $mappedLocation['id'],
                'authority' => [
                    'id' => '1',
                    'name' => 'Stadtverwaltung München',
                    'webinfo' => 'https://muenchen.de'
                ],
                'url' => str_replace(['{serviceId}', '{locationId}'], [$reference['refId'], $mappedLocation['id']], $this->publicUrl . "/services/{serviceId}/locations/{locationId}"),
                'appointment' => [
                    'link' => str_replace(['{serviceId}', '{locationId}'], [$reference['refId'], $mappedLocation['id']], $this->publicUrl . "/services/{serviceId}/locations/{locationId}"),
                    'slots' => '0',
                    'external' => false,
                    'multiple' => '1',
                    'allowed' => true
                ],
                'responsibility' => null,
                'responsibility_hint' => null,
                'hint' => false
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

            if ($durationCommonDivisor === null) {
                $durationCommonDivisor = $serviceRef['duration'];
            } else {
                $durationCommonDivisor = $this->getSlotTime($durationCommonDivisor, $serviceRef['duration']);
            }

            $mappedLocation['services'][] = $serviceRef;
            $mappedServices[$reference['refId']]['locations'][] = $locationRef;
        }

        return $durationCommonDivisor;
    }

    private function calculateSlotTimes(array &$mappedLocation, ?int $durationCommonDivisor): void
    {
        foreach ($mappedLocation['services'] as $key => $service) {
            if ($durationCommonDivisor && isset($service['duration'])) {
                $mappedLocation['services'][$key]['appointment']['slots'] = (string) ((int)($service['duration'] / $durationCommonDivisor));
            }
        }

        $mappedLocation['slotTimeInMinutes'] = $durationCommonDivisor;
        $mappedLocation['forceSlotTimeUpdate'] = true;
    }

    private function buildLocationResponse(array $mappedLocations): array
    {
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
     * Get service combinations (services that can be booked together).
     * Aligned with dldb-mapper map.php: if no SERVICE_COMBINATIONS row starts with this id,
     * return a singleton list so combinable is always present in mapped services JSON.
     */
    protected function getServiceCombinations(int $serviceId): array
    {
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

        return [(int) $serviceId];
    }

    /**
     * Calculate greatest common divisor for slot times
     */
    protected function getSlotTime(int $a, int $b): int
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
