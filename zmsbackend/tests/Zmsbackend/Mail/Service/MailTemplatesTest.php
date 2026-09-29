<?php

namespace BO\Zmsbackend\Tests\Mail\Service;

use BO\Zmsbackend\Mail\Exception\MailTemplateCopyInvalidInput;
use BO\Zmsbackend\Mail\Exception\MailTemplateCustomizationNotFound;
use BO\Zmsbackend\Mail\Service\MailTemplates;

class MailTemplatesTest extends \BO\Zmsbackend\Tests\Service\Base
{
    private const string SOURCE_PROVIDER = '122217';
    private const string FIRST_TARGET_PROVIDER = '122280';
    private const string SECOND_TARGET_PROVIDER = '122282';

    public function testCopiesCustomizationToMultipleProviders(): void
    {
        $service = new MailTemplates();
        $templateName = 'mail_copy_service_test.twig';
        $sourceTemplateId = $this->insertCustomization(
            $service,
            $templateName,
            'source content',
            self::SOURCE_PROVIDER
        );

        $copiedTemplate = $service->copyCustomizationToProviders(
            $sourceTemplateId,
            self::SOURCE_PROVIDER,
            [
                self::FIRST_TARGET_PROVIDER,
                self::SECOND_TARGET_PROVIDER,
                self::FIRST_TARGET_PROVIDER,
            ]
        );

        self::assertSame($sourceTemplateId, (int) $copiedTemplate->id);
        self::assertSame(
            'source content',
            $this->readCustomizationValue(
                $service,
                $templateName,
                self::FIRST_TARGET_PROVIDER
            )
        );
        self::assertSame(
            'source content',
            $this->readCustomizationValue(
                $service,
                $templateName,
                self::SECOND_TARGET_PROVIDER
            )
        );
        self::assertSame(
            1,
            $this->countCustomizations(
                $service,
                $templateName,
                self::FIRST_TARGET_PROVIDER
            )
        );
    }

    public function testOverwritesExistingCustomizationWithoutCreatingDuplicate(): void
    {
        $service = new MailTemplates();
        $templateName = 'mail_copy_overwrite_test.twig';
        $sourceTemplateId = $this->insertCustomization(
            $service,
            $templateName,
            'new content',
            self::SOURCE_PROVIDER
        );
        $this->insertCustomization(
            $service,
            $templateName,
            'old content',
            self::FIRST_TARGET_PROVIDER
        );

        $service->copyCustomizationToProviders(
            $sourceTemplateId,
            self::SOURCE_PROVIDER,
            [self::FIRST_TARGET_PROVIDER]
        );

        self::assertSame(
            'new content',
            $this->readCustomizationValue(
                $service,
                $templateName,
                self::FIRST_TARGET_PROVIDER
            )
        );
        self::assertSame(
            1,
            $this->countCustomizations(
                $service,
                $templateName,
                self::FIRST_TARGET_PROVIDER
            )
        );
    }

    public function testRejectsTemplateFromAnotherSourceProvider(): void
    {
        $service = new MailTemplates();
        $sourceTemplateId = $this->insertCustomization(
            $service,
            'mail_copy_wrong_source_test.twig',
            'source content',
            self::SOURCE_PROVIDER
        );

        $this->expectException(MailTemplateCustomizationNotFound::class);

        $service->copyCustomizationToProviders(
            $sourceTemplateId,
            '122999',
            [self::FIRST_TARGET_PROVIDER]
        );
    }

    public function testRejectsSourceProviderAsTargetProvider(): void
    {
        $service = new MailTemplates();
        $sourceTemplateId = $this->insertCustomization(
            $service,
            'mail_copy_invalid_target_test.twig',
            'source content',
            self::SOURCE_PROVIDER
        );

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $service->copyCustomizationToProviders(
            $sourceTemplateId,
            self::SOURCE_PROVIDER,
            [self::SOURCE_PROVIDER]
        );
    }

    public function testRejectsEmptyTargetProviderList(): void
    {
        $service = new MailTemplates();
        $sourceTemplateId = $this->insertCustomization(
            $service,
            'mail_copy_empty_targets_test.twig',
            'source content',
            self::SOURCE_PROVIDER
        );

        $this->expectException(MailTemplateCopyInvalidInput::class);

        $service->copyCustomizationToProviders(
            $sourceTemplateId,
            self::SOURCE_PROVIDER,
            []
        );
    }

    public function testValidatesAllTargetsBeforeWriting(): void
    {
        $service = new MailTemplates();
        $templateName = 'mail_copy_atomic_validation_test.twig';
        $sourceTemplateId = $this->insertCustomization(
            $service,
            $templateName,
            'source content',
            self::SOURCE_PROVIDER
        );

        try {
            $service->copyCustomizationToProviders(
                $sourceTemplateId,
                self::SOURCE_PROVIDER,
                [self::FIRST_TARGET_PROVIDER, '']
            );
            self::fail('Expected invalid target provider input.');
        } catch (MailTemplateCopyInvalidInput) {
            self::assertSame(
                0,
                $this->countCustomizations(
                    $service,
                    $templateName,
                    self::FIRST_TARGET_PROVIDER
                )
            );
        }
    }

    private function insertCustomization(
        MailTemplates $service,
        string $name,
        string $value,
        string $providerId
    ): int {
        $service->perform(
            'INSERT INTO mailtemplate '
            . '(name, value, provider, changeTimestamp) '
            . 'VALUES (:name, :value, :provider, CURRENT_TIMESTAMP)',
            [
                'name' => $name,
                'value' => $value,
                'provider' => $providerId,
            ]
        );

        return (int) $service->getWriter()->lastInsertId();
    }

    private function readCustomizationValue(
        MailTemplates $service,
        string $name,
        string $providerId
    ): string {
        return (string) $service->fetchValue(
            'SELECT value FROM mailtemplate '
            . 'WHERE name = :name AND provider = :provider',
            [
                'name' => $name,
                'provider' => $providerId,
            ]
        );
    }

    private function countCustomizations(
        MailTemplates $service,
        string $name,
        string $providerId
    ): int {
        return (int) $service->fetchValue(
            'SELECT COUNT(*) FROM mailtemplate '
            . 'WHERE name = :name AND provider = :provider',
            [
                'name' => $name,
                'provider' => $providerId,
            ]
        );
    }
}
