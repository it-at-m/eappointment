<?php

namespace BO\Zmsadmin\Tests;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class MailTemplatesCopyTest extends Base
{
    protected $classname = 'MailTemplatesCopy';

    public function testRendering(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render([], ['sourceScopeId' => 143]);
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
                'sourceScopeId' => 143,
                'success' => 'mailtemplates_copied',
                'copiedCount' => 1,
            ]
        );
        $body = (string) $response->getBody();

        self::assertSame(1, substr_count($body, 'Kopieren erfolgreich'));
        self::assertStringContainsString(
            'auf 1 Standort',
            preg_replace('/\s+/', ' ', $body) ?? $body
        );
    }

    public function testShowsPluralSuccessMessage(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 143,
                'success' => 'mailtemplates_copied',
                'copiedCount' => 2,
            ]
        );
        $body = preg_replace(
            '/\s+/',
            ' ',
            (string) $response->getBody()
        ) ?? '';

        self::assertStringContainsString('auf 2 Standorte', $body);
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
                'sourceScopeId' => 143,
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
            'sourceScopeId=143',
            $location
        );
        self::assertStringContainsString(
            'success=mailtemplates_copied',
            $location
        );
        self::assertStringContainsString('copiedCount=1', $location);
    }

    public function testShowsValidationErrorWhenNoTargetWasSelected(): void
    {
        $this->setApiCalls($this->getApiCallsForPage());

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 143,
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
        $this->setApiCalls(
            $this->getApiCallsForPage(
                false,
                true,
                '122251'
            )
        );

        $response = $this->render(
            [],
            [
                'sourceScopeId' => 146,
                'sourceTemplateId' => 901,
                'targetScopeIds' => [456],
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
            $this->getApiCallsForPage(true)
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

        $response = $this->render([], ['sourceScopeId' => 143]);
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
        string $sourceProviderId = '122280'
    ): array {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'parameters' => ['resolveReferences' => 3],
                'response' => $this->workstationResponse(
                    $withoutEligibleTargets
                ),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/owner/',
                'parameters' => ['resolveReferences' => 4],
                'response' => $this->readFixture(
                    'GET_ownerlist.json'
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
        bool $withoutEligibleTargets
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
            * ausgewählten Quellstandort. Dieser wird in Twig
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

        $setProvider = static function (
            array &$scope
        ) use ($providers): void {
            $scopeId = (int) $scope['id'];

            $scope['provider'] = [
                'id' => $providers[$scopeId],
                'source' => 'dldb',
            ];
        };

        foreach (
            $response['data']['useraccount']['departments']
            as &$department
        ) {
            foreach (
                $department['scopes'] ?? []
                as &$scope
            ) {
                $setProvider($scope);
            }
            unset($scope);

            foreach (
                $department['clusters'] ?? []
                as &$cluster
            ) {
                foreach (
                    $cluster['scopes'] ?? []
                    as &$scope
                ) {
                    $setProvider($scope);

                    $scope['contact']['name'] ??=
                        $cluster['name'];
                }
                unset($scope);
            }
            unset($cluster);
        }
        unset($department);

        return json_encode(
            $response,
            JSON_THROW_ON_ERROR
        );
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
