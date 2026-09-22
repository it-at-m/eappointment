<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class MailTemplatesCopyTest extends Base
{
    protected $classname = 'MailTemplatesCopy';

    public function testRendering(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render([], ['sourceScopeId' => 141]);
        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            'E-Mail-Template kopieren',
            $body
        );
        self::assertStringContainsString(
            'requestSubmit()',
            $body
        );
        self::assertStringContainsString(
            'id="targetScopeIds"',
            $body
        );
        self::assertStringContainsString(
            'multiple="multiple"',
            $body
        );
        self::assertStringContainsString(
            'id="select-all-target-scopes"',
            $body
        );
        self::assertStringContainsString(
            '<optgroup',
            $body
        );
        self::assertMatchesRegularExpression(
            '/<option[^>]+title="[^"]+"[^>]*>/',
            $body
        );
        self::assertStringContainsString(
            'Eine bereits vorhandene Anpassung desselben',
            $body
        );
        self::assertStringContainsString(
            'Weitere Standorte desselben Dienstleisters',
            $body
        );
        self::assertStringContainsString('Template kopieren', $body);
        self::assertSame(
            1,
            substr_count($body, 'Zurück zu den E-Mail-Templates')
        );
    }

    public function testShowsSuccessMessageExactlyOnce(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'success' => 'mailtemplates_copied',
                'copiedCount' => 1,
            ]
        );
        $body = (string) $response->getBody();

        self::assertSame(1, substr_count($body, 'Kopieren erfolgreich'));
        self::assertStringContainsString(
            'auf 1 Dienstleister',
            preg_replace('/\s+/', ' ', $body) ?? $body
        );
    }

    public function testShowsPluralSuccessMessage(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'success' => 'mailtemplates_copied',
                'copiedCount' => 2,
            ]
        );
        $body = preg_replace(
            '/\s+/',
            ' ',
            (string) $response->getBody()
        ) ?? '';

        self::assertStringContainsString('auf 2 Dienstleister', $body);
    }

    public function testRedirectsAfterSuccessfulCopy(): void
    {
        $apiCalls = $this->getApiCallsForPage();
        $apiCalls[] = [
            'function' => 'readPostResult',
            'url' => '/mailtemplates/copy/',
            'response' => $this->mailtemplateResponse(),
        ];
        $this->setApiCalls($apiCalls);

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [380],
                'copy' => '1',
            ],
            [],
            'POST'
        );

        self::assertSame(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        self::assertStringContainsString(
            'sourceScopeId=141',
            $location
        );
        self::assertStringContainsString(
            'success=mailtemplates_copied',
            $location
        );
        self::assertStringContainsString('copiedCount=1', $location);
    }

    public function testDeduplicatesTargetScopesByProvider(): void
    {
        $this->setApiCalls(
            $this->getApiCallsForPage(false, true, '122210')
        );

        $response = $this->render([], ['sourceScopeId' => 380]);
        $body = (string) $response->getBody();

        self::assertSame(
            1,
            preg_match(
                '/<select[^>]+id="targetScopeIds".*?<\/select>/s',
                $body,
                $matches
            )
        );

        $targetSelect = $matches[0];
        $hasScope141 = str_contains($targetSelect, 'value="141"');
        $hasScope1 = str_contains($targetSelect, 'value="1"');

        self::assertTrue(
            $hasScope141 xor $hasScope1,
            'Sibling locations of the same provider must appear once.'
        );
    }

    public function testCountsUniqueProvidersAfterCopy(): void
    {
        $apiCalls = $this->getApiCallsForPage(
            false,
            true,
            '122210'
        );
        $apiCalls[] = [
            'function' => 'readPostResult',
            'url' => '/mailtemplates/copy/',
            'response' => $this->mailtemplateResponse(),
        ];
        $this->setApiCalls($apiCalls);

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 380,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [141, 1],
                'copy' => '1',
            ],
            [],
            'POST'
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString(
            'copiedCount=1',
            $response->getHeaderLine('Location')
        );
    }

    public function testShowsBackendErrorWhenCopyFails(): void
    {
        $exception = new \BO\Zmsclient\Exception(
            'API-Error: missing rights'
        );
        $exception->originalMessage =
            'Sie verfügen nicht über die notwendigen Rechte.';

        $apiCalls = $this->getApiCallsForPage();
        $apiCalls[] = [
            'function' => 'readPostResult',
            'url' => '/mailtemplates/copy/',
            'exception' => $exception,
        ];
        $this->setApiCalls($apiCalls);

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [380],
                'copy' => '1',
            ],
            [],
            'POST'
        );
        $body = (string) $response->getBody();

        self::assertStringContainsString(
            'Sie verfügen nicht über die notwendigen Rechte.',
            $body
        );
        self::assertStringNotContainsString(
            'Es wurden keine Änderungen übernommen.',
            $body
        );
    }

    public function testShowsValidationErrorWhenNoTargetWasSelected(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [],
                'copy' => '1',
            ],
            [],
            'POST'
        );

        self::assertStringContainsString(
            'Bitte wählen Sie mindestens einen Zielstandort aus.',
            (string) $response->getBody()
        );
    }

    public function testShowsDistinctErrorForDifferentScopeWithSameProvider(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 141,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [1],
                'copy' => '1',
            ],
            [],
            'POST'
        );

        $body = preg_replace(
            '/\s+/',
            ' ',
            (string) $response->getBody()
        ) ?? '';

        self::assertStringContainsString(
            'Der ausgewählte Zielstandort verwendet '
                . 'denselben Provider wie der Quellstandort',
            $body
        );

        self::assertStringNotContainsString(
            'Der Quellstandort darf nicht gleichzeitig '
                . 'als Zielstandort ausgewählt werden.',
            $body
        );
    }

    public function testHidesCopyActionWhenNoEligibleTargetScopesExist(): void
    {
        $this->setApiCalls(
            $this->getApiCallsForPage(true, true, '122280')
        );

        $response = $this->render([], ['sourceScopeId' => 143]);
        $body = (string) $response->getBody();

        self::assertStringContainsString(
            'Keine Zielstandorte verfügbar',
            $body
        );
        self::assertStringNotContainsString(
            'name="targetScopeIds[]"',
            $body
        );
        self::assertStringNotContainsString(
            'name="copy"',
            $body
        );
        self::assertSame(
            1,
            substr_count($body, 'Zurück zu den E-Mail-Templates')
        );
    }

    public function testShowsMessageWhenSourceHasNoCustomTemplates(): void
    {
        $this->setApiCalls($this->getApiCallsForPage(false, false));

        $response = $this->render([], ['sourceScopeId' => 141]);
        $body = (string) $response->getBody();

        self::assertStringContainsString(
            'sind keine angepassten E-Mail-Templates vorhanden',
            preg_replace('/\s+/', ' ', $body) ?? $body
        );
        self::assertStringNotContainsString('name="copy"', $body);
        self::assertSame(
            1,
            substr_count($body, 'Zurück zu den E-Mail-Templates')
        );
    }

    public function testIgnoresScopesWithoutProvider(): void
    {
        $this->setApiCalls(
            $this->getApiCallsForPage(false, true, '122217', true)
        );

        $response = $this->render([], ['sourceScopeId' => 141]);
        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            'E-Mail-Template kopieren',
            $body
        );
        self::assertStringNotContainsString(
            'kein Dienstleister zugeordnet',
            $body
        );
        self::assertStringNotContainsString('value="29"', $body);
    }

    public function testRequiresMailtemplatePermission(): void
    {
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'parameters' => ['resolveReferences' => 3],
                'response' => $this->readFixture(
                    'GET_workstationlist_department_74.json'
                ),
            ],
        ]);

        $this->expectException(UserAccountMissingRights::class);

        $this->render();
    }

    private function getApiCallsForPage(
        bool $withoutEligibleTargets = false,
        bool $hasCustomTemplates = true,
        string $sourceProviderId = '122217',
        bool $withMissingProviderScope = false
    ): array {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'parameters' => ['resolveReferences' => 3],
                'response' => $this->workstationResponse(
                    $withoutEligibleTargets,
                    $withMissingProviderScope
                ),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/custom-mailtemplates/'
                    . $sourceProviderId
                    . '/',
                'response' => $this->mailtemplateResponse(
                    true,
                    $hasCustomTemplates,
                    $sourceProviderId
                ),
            ],
        ];
    }

    private function workstationResponse(
        bool $withoutEligibleTargets,
        bool $withMissingProviderScope = false
    ): string {
        $response = json_decode(
            $this->readFixture(
                'GET_Workstation_Resolved2.json'
            ),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if ($withoutEligibleTargets) {
            /*
            * Die Department-Liste enthält bewusst nur den
            * ausgewählten Quellstandort. Dieser wird in PHP
            * aus der Zielauswahl ausgeschlossen.
            */
            $response['data']['useraccount']['departments'] = [
                [
                    'id' => 75,
                    'name' => 'Test Department',
                    'scopes' => [
                        [
                            'id' => 143,
                            'source' => 'dldb',
                            'contact' => [
                                'name' =>
                                    'Bürgeramt Rathaus Mitte',
                            ],
                            'provider' => [
                                'id' => '122280',
                                'source' => 'dldb',
                            ],
                            'shortName' => '',
                        ],
                    ],
                    'clusters' => [],
                ],
            ];

            return json_encode(
                $response,
                JSON_THROW_ON_ERROR
            );
        }

        $providers = [
            1 => '122217',
            140 => '122219',
            141 => '122217',
            142 => '122227',
            380 => '122210',
        ];

        foreach (
            $response['data']['useraccount']['departments']
            as $departmentIndex => $department
        ) {
            foreach (
                $department['scopes'] ?? []
                as $scopeIndex => $scope
            ) {
                $this->assignTestProvider(
                    $response['data']['useraccount']
                        ['departments']
                        [$departmentIndex]
                        ['scopes']
                        [$scopeIndex],
                    $providers
                );
            }

            foreach (
                $department['clusters'] ?? []
                as $clusterIndex => $cluster
            ) {
                foreach (
                    $cluster['scopes'] ?? []
                    as $scopeIndex => $scope
                ) {
                    $this->assignTestProvider(
                        $response['data']['useraccount']
                            ['departments']
                            [$departmentIndex]
                            ['clusters']
                            [$clusterIndex]
                            ['scopes']
                            [$scopeIndex],
                        $providers
                    );

                    $response['data']['useraccount']
                        ['departments']
                        [$departmentIndex]
                        ['clusters']
                        [$clusterIndex]
                        ['scopes']
                        [$scopeIndex]
                        ['contact']
                        ['name']
                        ??= $cluster['name'] ?? '';
                }
            }
        }

        if ($withMissingProviderScope) {
            array_unshift(
                $response['data']['useraccount']
                    ['departments'][0]['scopes'],
                [
                    'id' => 29,
                    'contact' => [
                        'name' => 'Ohne Dienstleister',
                    ],
                ]
            );
        }

        return json_encode(
            $response,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param array<int, string> $providers
     */
    private function assignTestProvider(
        array &$scope,
        array $providers
    ): void {
        $scopeId = (int) ($scope['id'] ?? 0);

        if (!isset($providers[$scopeId])) {
            return;
        }

        $scope['provider'] = [
            'id' => $providers[$scopeId],
            'source' => 'dldb',
        ];
    }

    private function mailtemplateResponse(
        bool $asCollection = false,
        bool $hasTemplate = true,
        string $providerId = '122280'
    ): string {
        $template = [
            '$schema' => 'https://schema.berlin.de/'
                . 'queuemanagement/mailtemplate.json',
            'id' => 901,
            'name' => 'mail_preconfirmed.twig',
            'value' => 'Customized template content',
            'provider' => $providerId,
        ];

        $data = $hasTemplate ? $template : [];
        if ($asCollection && $hasTemplate) {
            $data = [$template];
        }

        return json_encode(
            [
                '$schema' => 'https://localhost/terminvereinbarung/api/2/',
                'meta' => [
                    '$schema' => 'https://schema.berlin.de/'
                        . 'queuemanagement/metaresult.json',
                    'error' => false,
                    'generated' => '2026-09-18T10:00:00+00:00',
                    'server' => 'Zmsbackend-UNITTEST',
                ],
                'data' => $data,
            ],
            JSON_THROW_ON_ERROR
        );
    }
}
