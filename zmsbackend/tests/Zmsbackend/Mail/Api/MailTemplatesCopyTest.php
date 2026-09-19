<?php

namespace BO\Zmsbackend\Tests\Mail\Api;

use BO\Zmsbackend\Mail\Exception\MailTemplateCopyInvalidInput;
use BO\Zmsbackend\Mail\Service\MailTemplates;
use BO\Zmsentities\Department;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Scope;

class MailTemplatesCopyTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = 'MailTemplatesCopy';

    public function testRendering(): void
    {
        $sourceTemplateId = $this->insertSourceCustomization(
            'mail_copy_api_test.twig'
        );
        $this->setAuthorizedWorkstation([141, 143, 144]);

        $response = $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [143, 144],
        ]);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString(
            'mail_copy_api_test.twig',
            (string) $response->getBody()
        );
        self::assertSame(
            'copied content',
            $this->readCustomizationValue(
                'mail_copy_api_test.twig',
                '122280'
            )
        );
        self::assertSame(
            'copied content',
            $this->readCustomizationValue(
                'mail_copy_api_test.twig',
                '122282'
            )
        );
    }

    public function testRejectsEmptyTargetScopeList(): void
    {
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => 1,
            'targetScopeIds' => [],
        ]);
    }

    public function testRejectsSourceScopeAsTargetScope(): void
    {
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => 1,
            'targetScopeIds' => [141],
        ]);
    }

    public function testRejectsDifferentScopeWithSameProvider(): void
    {
        $scenario = $this->readProviderAccessScenario();

        $this->setAuthorizedWorkstation([
            $scenario['firstProviderScopeId'],
            $scenario['secondProviderScopeId'],
        ]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' =>
                $scenario['firstProviderScopeId'],
            'sourceTemplateId' => 1,
            'targetScopeIds' => [
                $scenario['secondProviderScopeId'],
            ],
        ]);
    }

    public function testRequiresMailtemplatePermission(): void
    {
        $this->setWorkstation();

        $this->expectException(UserAccountMissingRights::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => 1,
            'targetScopeIds' => [143],
        ]);
    }

    public function testRejectsInaccessibleTargetScope(): void
    {
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(UserAccountMissingRights::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => 1,
            'targetScopeIds' => [143],
        ]);
    }

    public function testRejectsProviderWhenAnotherProviderScopeIsInaccessible(): void
    {
        $scenario = $this->readProviderAccessScenario();

        /*
        * The account may access the selected target scope, but not another
        * scope using the same provider. A provider-level write must fail.
        */
        $this->setAuthorizedWorkstation([
            $scenario['sourceScopeId'],
            $scenario['firstProviderScopeId'],
        ]);

        $this->expectException(UserAccountMissingRights::class);

        $this->renderJson([
            'sourceScopeId' => $scenario['sourceScopeId'],
            'sourceTemplateId' => 1,
            'targetScopeIds' => [
                $scenario['firstProviderScopeId'],
            ],
        ]);
    }

    /**
     * @return array{
     *     sourceScopeId: int,
     *     firstProviderScopeId: int,
     *     secondProviderScopeId: int
     * }
     */
    private function readProviderAccessScenario(): array
    {
        $service = new MailTemplates();

        $targetProviderRow = $service->fetchRow(
            'SELECT InfoDienstleisterID AS providerId '
            . 'FROM standort '
            . 'WHERE InfoDienstleisterID > 0 '
            . 'GROUP BY InfoDienstleisterID '
            . 'HAVING COUNT(*) > 1 '
            . 'ORDER BY InfoDienstleisterID '
            . 'LIMIT 1'
        );

        if (!is_array($targetProviderRow)) {
            self::fail(
                'No provider with at least two scopes was found.'
            );
        }

        $targetProviderId = (string) (
            $targetProviderRow['providerId'] ?? ''
        );

        self::assertNotSame('', $targetProviderId);

        $providerScopeRows = $service->fetchAll(
            'SELECT StandortID AS scopeId '
            . 'FROM standort '
            . 'WHERE InfoDienstleisterID = :providerId '
            . 'ORDER BY StandortID '
            . 'LIMIT 2',
            [
                'providerId' => $targetProviderId,
            ]
        );

        self::assertCount(
            2,
            $providerScopeRows,
            'The selected provider must have at least two scopes.'
        );

        $firstProviderScopeId = (int) (
            $providerScopeRows[0]['scopeId'] ?? 0
        );
        $secondProviderScopeId = (int) (
            $providerScopeRows[1]['scopeId'] ?? 0
        );

        $sourceScopeId = (int) $service->fetchValue(
            'SELECT StandortID '
            . 'FROM standort '
            . 'WHERE InfoDienstleisterID > 0 '
            . 'AND InfoDienstleisterID <> :providerId '
            . 'ORDER BY StandortID '
            . 'LIMIT 1',
            [
                'providerId' => $targetProviderId,
            ]
        );

        self::assertGreaterThan(0, $sourceScopeId);
        self::assertGreaterThan(0, $firstProviderScopeId);
        self::assertGreaterThan(0, $secondProviderScopeId);
        self::assertNotSame(
            $firstProviderScopeId,
            $secondProviderScopeId
        );

        return [
            'sourceScopeId' => $sourceScopeId,
            'firstProviderScopeId' => $firstProviderScopeId,
            'secondProviderScopeId' => $secondProviderScopeId,
        ];
    }

    private function renderJson(array $input): \Psr\Http\Message\ResponseInterface
    {
        return $this->render(
            [],
            [
                '__body' => json_encode(
                    $input,
                    JSON_THROW_ON_ERROR
                ),
            ],
            [],
            'POST'
        );
    }

    private function setAuthorizedWorkstation(array $scopeIds): void
    {
        $department = new Department([
            'id' => 999,
            'name' => 'Mail template copy test department',
        ]);

        foreach ($scopeIds as $scopeId) {
            $department->scopes[] = new Scope(['id' => $scopeId]);
        }

        $this->setWorkstation()
            ->getUseraccount()
            ->setPermissions('mailtemplates')
            ->addDepartment($department);
    }

    private function insertSourceCustomization(string $name): int
    {
        return $this->insertCustomization($name, '122217');
    }

    private function insertCustomization(
        string $name,
        string $providerId
    ): int {
        $service = new MailTemplates();
        $service->perform(
            'INSERT INTO mailtemplate '
            . '(name, value, provider, changeTimestamp) '
            . 'VALUES (:name, :value, :provider, CURRENT_TIMESTAMP)',
            [
                'name' => $name,
                'value' => 'copied content',
                'provider' => $providerId,
            ]
        );

        $templateId = (int) $service
            ->getWriter()
            ->lastInsertId();

        self::assertGreaterThan(
            0,
            $templateId,
            'The inserted mail-template ID must be greater than zero.'
        );

        return $templateId;
    }

    private function readCustomizationValue(
        string $name,
        string $providerId
    ): string {
        return (string) (new MailTemplates())->fetchValue(
            'SELECT value FROM mailtemplate '
            . 'WHERE name = :name AND provider = :provider',
            [
                'name' => $name,
                'provider' => $providerId,
            ]
        );
    }
}
