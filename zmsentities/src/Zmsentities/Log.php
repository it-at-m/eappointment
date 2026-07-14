<?php

namespace BO\Zmsentities;

class Log extends Schema\Entity
{
    public const PRIMARY = 'reference';

    public const ACTION_MAIL_SUCCESS = 'E-Mail-Versand erfolgreich';
    public const ACTION_MAIL_FAIL = 'E-Mail-Versand ist fehlgeschlagen';
    public const ACTION_STATUS_CHANGE = 'Terminstatus wurde geändert';
    public const ACTION_SEND_REMINDER = 'Erinnerungsmail wurde gesendet';
    public const ACTION_REMOVED = 'Termin aus der Warteschlange entfernt';
    public const ACTION_CALLED = 'Termin wurde aufgerufen';
    public const ACTION_ARCHIVED = 'Termin wurde archiviert';
    public const ACTION_EDITED = 'Termin wurde geändert';
    public const ACTION_REDIRECTED = 'Termin wurde weitergeleitet';
    public const ACTION_NEW = 'Neuer Termin wurde erstellt';
    public const ACTION_DELETED = 'Termin wurde gelöscht';
    public const ACTION_CANCELED = 'Termin wurde abgesagt';

    private const ACTION_CODE_TO_LABEL = [
        'mail_success' => self::ACTION_MAIL_SUCCESS,
        'mail_fail' => self::ACTION_MAIL_FAIL,
        'status_changed' => self::ACTION_STATUS_CHANGE,
        'reminder_sent' => self::ACTION_SEND_REMINDER,
        'removed_from_queue' => self::ACTION_REMOVED,
        'called' => self::ACTION_CALLED,
        'archived' => self::ACTION_ARCHIVED,
        'edited' => self::ACTION_EDITED,
        'redirected' => self::ACTION_REDIRECTED,
        'created' => self::ACTION_NEW,
        'deleted' => self::ACTION_DELETED,
        'canceled' => self::ACTION_CANCELED,
    ];

    private const DISPLAY_TEXT_FIELDS = [
        'user_id' => 'Sachbearbeiter*in',
        'display_number' => 'Terminnummer',
        'citizen_name' => 'Bürger*in',
        'services' => 'Dienstleistungen',
        'process_amendment' => 'Anmerkung',
        'scope_name' => 'Standort',
        'citizen_email' => 'E-Mail',
        'citizen_phone' => 'Telefon',
        'process_status' => 'Status',
        'db_status' => 'DB Status',
    ];

    private const DISPLAY_OPTIONAL_NUMBER_FIELDS = [
        'queue_number' => 'Wartenummer',
        'slot_count' => 'Slots',
    ];

    public static $schema = "log.json";

    public static function actionLabelFromCode(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return self::ACTION_CODE_TO_LABEL[$code] ?? null;
    }

    public static function formatDisplayFields(array $log): array
    {
        $display = [];
        $actionLabel = self::actionLabelFromCode($log['action'] ?? null);
        if ($actionLabel !== null) {
            $display['Aktion'] = $actionLabel;
        }

        foreach (self::DISPLAY_TEXT_FIELDS as $column => $label) {
            if (!empty($log[$column])) {
                $display[$label] = $log[$column];
            }
        }

        foreach (self::DISPLAY_OPTIONAL_NUMBER_FIELDS as $column => $label) {
            if (isset($log[$column]) && $log[$column] !== '') {
                $display[$label] = $log[$column];
            }
        }

        $appointmentAt = self::formatAppointmentAtDisplay($log['appointment_at'] ?? null);
        if ($appointmentAt !== null) {
            $display['Terminzeit'] = $appointmentAt;
        }

        return $display;
    }

    private static function formatAppointmentAtDisplay($appointmentAt): ?string
    {
        if (empty($appointmentAt)) {
            return null;
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $appointmentAt);
        if (!$parsed instanceof \DateTimeImmutable) {
            return null;
        }

        return $parsed->format('d.m.Y H:i:s');
    }
}
