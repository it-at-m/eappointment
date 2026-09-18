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
        $sourceTemplateId = $this->insertSourceCustomization(
            'mail_copy_empty_api_test.twig'
        );
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [],
        ]);
    }

    public function testRejectsSourceScopeAsTargetScope(): void
    {
        $sourceTemplateId = $this->insertSourceCustomization(
            'mail_copy_source_target_api_test.twig'
        );
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [141],
        ]);
    }

    public function testRejectsDifferentScopeWithSameProvider(): void
    {
        $sourceTemplateId = $this->insertCustomization(
            'mail_copy_same_provider_api_test.twig',
            '122251'
        );
        $this->setAuthorizedWorkstation([146, 456]);

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $this->renderJson([
            'sourceScopeId' => 146,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [456],
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
        $sourceTemplateId = $this->insertSourceCustomization(
            'mail_copy_scope_access_api_test.twig'
        );
        $this->setAuthorizedWorkstation([141]);

        $this->expectException(UserAccountMissingRights::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [143],
        ]);
    }

    public function testRejectsProviderWhenAnotherProviderScopeIsInaccessible(): void
    {
        $sourceTemplateId = $this->insertSourceCustomization(
            'mail_copy_provider_access_api_test.twig'
        );

        /*
         * Scopes 169 and 441 use provider 122291. The account may access
         * scope 169, but not scope 441, so a provider-level write must fail.
         */
        $this->setAuthorizedWorkstation([141, 169]);

        $this->expectException(UserAccountMissingRights::class);

        $this->renderJson([
            'sourceScopeId' => 141,
            'sourceTemplateId' => $sourceTemplateId,
            'targetScopeIds' => [169],
        ]);
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

        return (int) $service->getWriter()->lastInsertId();
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
