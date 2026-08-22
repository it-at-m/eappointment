<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Helper;

class AppointmentByIdRows
{
    /**
     * @return array<string, mixed>
     */
    public static function processRow(): array
    {
        return [
            'process_id' => 101002,
            'auth_key' => 'fb43',
            'external_user_id' => null,
            'family_name' => 'Doe',
            'custom_textfield' => '',
            'custom_textfield2' => '',
            'email' => 'johndoe@example.com',
            'telephone' => '0123456789',
            'appointment_datetime' => '2024-08-29 07:00:00',
            'slot_count' => 1,
            'display_number' => 0,
            'status' => 'confirmed',
            'id' => 102522,
            'source' => 'dldb',
            'name' => 'Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)',
            'display_name' => 'Bürgerbüro Orleansplatz DEV',
            'data' => [],
            'contact__city' => 'Muenchen',
            'contact__country' => 'Germany',
            'contact__lat' => null,
            'contact__lon' => null,
            'contact__postalCode' => '81667',
            'contact__region' => 'Muenchen',
            'contact__street' => 'Orleansstraße',
            'contact__streetNumber' => '50',
            'scope_id' => 64,
            'scope_short_name' => 'DEVV',
            'scope_email_from' => 'no-reply@muenchen.de',
            'scope_email_required' => 1,
            'scope_telephone_activated' => 1,
            'scope_telephone_required' => 1,
            'scope_custom_textfield_activated' => 1,
            'scope_custom_textfield_required' => 1,
            'scope_custom_textfield_label' => 'Nachname des Kindes',
            'scope_custom_textfield2_activated' => 1,
            'scope_custom_textfield2_required' => 1,
            'scope_custom_textfield2_label' => 'Zusätzliche Bemerkung',
            'scope_captcha_activated_required' => 0,
            'scope_info_for_appointment' => '',
            'scope_info_for_all_appointments' => '',
            'scope_slots_per_appointment' => '',
            'scope_appointments_per_mail' => 1,
            'scope_whitelisted_mails' => '',
            'scope_reservation_duration' => 15,
            'scope_activation_duration' => 15,
            'scope_hint' => '',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function requestRows(): array
    {
        return [
            [
                'id' => '1063424',
                'name' => 'Gewerbe anmelden',
                'source' => 'dldb',
            ],
        ];
    }
}
