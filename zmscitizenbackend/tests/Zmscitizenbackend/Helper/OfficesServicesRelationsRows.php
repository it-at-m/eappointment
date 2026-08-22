<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Helper;

class OfficesServicesRelationsRows
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function officeRows(): array
    {
        return [
            self::officeRow(
                id: '9999998',
                name: 'Unittest Source Dienstleister',
                displayName: 'Unittest',
                streetNumber: '105',
                showAlternativeLocations: false,
                scopeId: 1,
                shortName: 'Scope 1',
                emailRequired: 0,
                telephoneActivated: 1,
                telephoneRequired: 0,
                customTextfieldActivated: 1,
                customTextfieldRequired: 0,
                customTextfieldLabel: 'Custom Label',
                customTextfield2Activated: 1,
                customTextfield2Required: 0,
                customTextfield2Label: 'Second Custom Label'
            ),
            self::officeRow(
                id: '9999999',
                name: 'Unittest Source Dienstleister 2',
                displayName: 'Unittest 2',
                streetNumber: '106',
                showAlternativeLocations: true,
                scopeId: 2,
                shortName: 'Scope 2',
                emailRequired: 1,
                telephoneActivated: 0,
                telephoneRequired: 1,
                customTextfieldActivated: 0,
                customTextfieldRequired: 1,
                customTextfieldLabel: '',
                customTextfield2Activated: 0,
                customTextfield2Required: 1,
                customTextfield2Label: ''
            ),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function requestRows(): array
    {
        return [
            [
                'id' => '1',
                'source' => 'unittest',
                'name' => 'Unittest Source Dienstleistung',
                'parent_id' => null,
                'variant_id' => null,
                'data' => json_encode(['json' => 'data']),
            ],
            [
                'id' => '2',
                'source' => 'unittest',
                'name' => 'Unittest Source Dienstleistung 2',
                'parent_id' => null,
                'variant_id' => null,
                'data' => json_encode([
                    'json' => 'data',
                    'key' => 'value',
                    'combinable' => ['1', '2'],
                    'public' => false,
                ]),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function relationRows(): array
    {
        return [
            [
                'provider__id' => '9999998',
                'request__id' => '1',
                'source' => 'unittest',
                'slots' => '2',
                'public_visibility' => 1,
                'max_quantity' => null,
            ],
            [
                'provider__id' => '9999999',
                'request__id' => '1',
                'source' => 'unittest',
                'slots' => '1',
                'public_visibility' => 1,
                'max_quantity' => null,
            ],
            [
                'provider__id' => '9999999',
                'request__id' => '2',
                'source' => 'unittest',
                'slots' => '1',
                'public_visibility' => 1,
                'max_quantity' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function officeRow(
        string $id,
        string $name,
        string $displayName,
        string $streetNumber,
        bool $showAlternativeLocations,
        int $scopeId,
        string $shortName,
        int $emailRequired,
        int $telephoneActivated,
        int $telephoneRequired,
        int $customTextfieldActivated,
        int $customTextfieldRequired,
        string $customTextfieldLabel,
        int $customTextfield2Activated,
        int $customTextfield2Required,
        string $customTextfield2Label
    ): array {
        return [
            'id' => $id,
            'source' => 'unittest',
            'name' => $name,
            'display_name' => $displayName,
            'parent_id' => null,
            'data' => json_encode([
                'json' => 'data',
                'geo' => [
                    'lat' => '48.12750898398659',
                    'lon' => '11.604317899956524',
                ],
                'showAlternativeLocations' => $showAlternativeLocations,
            ]),
            'contact__city' => 'Berlin',
            'contact__country' => 'Germany',
            'contact__lat' => 48.12750898398659,
            'contact__lon' => 11.604317899956524,
            'contact__postalCode' => '10178',
            'contact__region' => 'Berlin',
            'contact__street' => 'Alte Jakobstraße',
            'contact__streetNumber' => $streetNumber,
            'scope_id' => $scopeId,
            'scope_short_name' => $shortName,
            'scope_email_from' => 'no-reply@muenchen.de',
            'scope_email_required' => $emailRequired,
            'scope_telephone_activated' => $telephoneActivated,
            'scope_telephone_required' => $telephoneRequired,
            'scope_custom_textfield_activated' => $customTextfieldActivated,
            'scope_custom_textfield_required' => $customTextfieldRequired,
            'scope_custom_textfield_label' => $customTextfieldLabel,
            'scope_custom_textfield2_activated' => $customTextfield2Activated,
            'scope_custom_textfield2_required' => $customTextfield2Required,
            'scope_custom_textfield2_label' => $customTextfield2Label,
            'scope_captcha_activated_required' => 0,
            'scope_info_for_appointment' => null,
            'scope_info_for_all_appointments' => null,
            'scope_slots_per_appointment' => null,
            'scope_appointments_per_mail' => null,
            'scope_whitelisted_mails' => null,
            'scope_reservation_duration' => null,
            'scope_activation_duration' => null,
            'scope_hint' => '',
        ];
    }
}
