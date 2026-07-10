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
        if (!empty($log['user_id'])) {
            $display['Sachbearbeiter*in'] = $log['user_id'];
        }
        if (!empty($log['display_number'])) {
            $display['Terminnummer'] = $log['display_number'];
        }
        if (isset($log['queue_number']) && $log['queue_number'] !== '') {
            $display['Wartenummer'] = $log['queue_number'];
        }
        if (!empty($log['appointment_at'])) {
            $appointmentAt = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                (string) $log['appointment_at']
            );
            if ($appointmentAt instanceof \DateTimeImmutable) {
                $display['Terminzeit'] = $appointmentAt->format('d.m.Y H:i:s');
            }
        }
        if (isset($log['slot_count']) && $log['slot_count'] !== '') {
            $display['Slots'] = $log['slot_count'];
        }
        if (!empty($log['citizen_name'])) {
            $display['Bürger*in'] = $log['citizen_name'];
        }
        if (!empty($log['services'])) {
            $display['Dienstleistungen'] = $log['services'];
        }
        if (!empty($log['citizen_amendment'])) {
            $display['Anmerkung'] = $log['citizen_amendment'];
        }
        if (!empty($log['scope_name'])) {
            $display['Standort'] = $log['scope_name'];
        }
        if (!empty($log['citizen_email'])) {
            $display['E-Mail'] = $log['citizen_email'];
        }
        if (!empty($log['citizen_phone'])) {
            $display['Telefon'] = $log['citizen_phone'];
        }
        if (!empty($log['process_status'])) {
            $display['Status'] = $log['process_status'];
        }
        if (!empty($log['db_status'])) {
            $display['DB Status'] = $log['db_status'];
        }

        return $display;
    }
}
