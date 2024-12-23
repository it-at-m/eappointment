<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Localization;

class ErrorMessages
{
    // English messages
    public const EN = [
        // Existing examples
        'ERR_NO_APPOINTMENTS'                  => 'No appointments available at the specified location.',
        'ERR_INVALID_PARAMS'                   => 'One or more parameters are invalid.',
        'ERR_APPOINTMENT_NOT_FOUND'            => 'The requested appointment could not be found.',
        'ERR_TOO_MANY_APPOINTMENTS'            => 'Too many appointments with the same email address.',
        'ERR_OFFICE_NOT_FOUND'                 => 'Office not found.',
        'ERR_SERVICE_NOT_FOUND'                => 'Service not found.',
        'ERR_SCOPE_NOT_FOUND'                  => 'Scope not found.',
        'ERR_APPOINTMENT_DAYS_NOT_FOUND'       => 'No appointment days found.',
        'ERR_CAPTCHA_FAILED'                   => 'Captcha verification failed.',
        'ERR_MAINTENANCE_MODE_ENABLED'         => 'Maintenance mode is enabled.',
        'ERR_INVALID_TIMESTAMP_FORMAT'         => 'Invalid timestamp format.',
        'ERR_CUSTOM_TEXT_REQUIRED'             => 'Custom textfield is required.',
        'ERR_NOT_ACCEPTABLE'                   => 'The request is not acceptable.',
        'ERR_NOT_FOUND'                        => 'Resource not found.',
        'ERR_EMAIL_IN_USE'                     => 'The email address is already used for an appointment.',

        // Added from ValidationService
        'invalidLocationAndServiceCombination' => 'The provided service(s) do not exist at the given location.',
        'invalidStartDate'                     => 'startDate is required and must be a valid date.',
        'invalidStartDateFormat'               => 'startDate must be in YYYY-MM-DD format.',
        'invalidEndDate'                       => 'endDate is required and must be a valid date.',
        'invalidEndDateFormat'                 => 'endDate must be in YYYY-MM-DD format.',
        'invalidOfficeId'                      => 'officeId should be a 32-bit integer.',
        'invalidServiceId'                     => 'serviceId should be a 32-bit integer.',
        'invalidServiceCount'                  => 'serviceCounts should be an array of numeric values.',
        'invalidProcessId'                     => 'processId should be a positive 32-bit integer.',
        'invalidAuthKey'                       => 'authKey should be a string.',
        'invalidDate'                          => 'date is required and must be a valid date.',
        'invalidTimestamp'                     => 'Missing timestamp or invalid timestamp format. It should be a positive numeric value.',
        'appointmentNotAvailable'              => 'The selected appointment is unfortunately no longer available.',
        'scopesNotFound'                       => 'Scope(s) not found.',
        'servicesNotFound'                     => 'Service(s) not found for the provided officeId(s).',
        'officesNotFound'                      => 'Office(s) not found for the provided serviceId(s).',
        'noAppointmentForThisDay'              => 'No available days found for the given criteria.',
        'noAppointmentForThisScope'            => 'There are currently no free appointments available at this location.',
        'invalidFamilyName'                    => 'familyName should be a non-empty string.',
        'invalidEmail'                         => 'email should be a valid email address.',
        'invalidTelephone'                     => 'telephone should be a numeric string between 7 and 15 digits.',
        'invalidCustomTextfield'               => 'customTextfield should be a string.',
    ];

    // German messages
    public const DE = [
        // Existing examples
        'ERR_NO_APPOINTMENTS'                  => 'Keine Termine verfügbar am angegebenen Standort.',
        'ERR_INVALID_PARAMS'                   => 'Mindestens ein Parameter ist ungültig.',
        'ERR_APPOINTMENT_NOT_FOUND'            => 'Der Termin konnte nicht gefunden werden.',
        'ERR_TOO_MANY_APPOINTMENTS'            => 'Zu viele Termine mit derselben E-Mail-Adresse.',
        'ERR_OFFICE_NOT_FOUND'                 => 'Der Standort wurde nicht gefunden.',
        'ERR_SERVICE_NOT_FOUND'                => 'Service wurde nicht gefunden.',
        'ERR_SCOPE_NOT_FOUND'                  => 'Scope wurde nicht gefunden.',
        'ERR_APPOINTMENT_DAYS_NOT_FOUND'       => 'Keine Termintage gefunden.',
        'ERR_CAPTCHA_FAILED'                   => 'Die Captcha-Verifizierung ist fehlgeschlagen.',
        'ERR_MAINTENANCE_MODE_ENABLED'         => 'Wartungsmodus ist aktiviert.',
        'ERR_INVALID_TIMESTAMP_FORMAT'         => 'Ungültiges Zeitstempelformat.',
        'ERR_CUSTOM_TEXT_REQUIRED'             => 'Freitext ist erforderlich.',
        'ERR_NOT_ACCEPTABLE'                   => 'Die Anfrage ist nicht akzeptabel.',
        'ERR_NOT_FOUND'                        => 'Ressource nicht gefunden.',
        'ERR_EMAIL_IN_USE'                     => 'Die E-Mail-Adresse wird bereits für einen Termin verwendet.',

        // Added from ValidationService
        'invalidLocationAndServiceCombination' => 'Die angegebene Dienstleistung ist an diesem Standort nicht verfügbar.',
        'invalidStartDate'                     => 'startDate ist erforderlich und muss ein gültiges Datum sein.',
        'invalidStartDateFormat'               => 'startDate muss im Format YYYY-MM-DD vorliegen.',
        'invalidEndDate'                       => 'endDate ist erforderlich und muss ein gültiges Datum sein.',
        'invalidEndDateFormat'                 => 'endDate muss im Format YYYY-MM-DD vorliegen.',
        'invalidOfficeId'                      => 'officeId muss eine 32-Bit-Ganzzahl sein.',
        'invalidServiceId'                     => 'serviceId muss eine 32-Bit-Ganzzahl sein.',
        'invalidServiceCount'                  => 'serviceCounts muss ein Array aus numerischen Werten sein.',
        'invalidProcessId'                     => 'processId muss eine positive 32-Bit-Ganzzahl sein.',
        'invalidAuthKey'                       => 'authKey muss eine Zeichenkette sein.',
        'invalidDate'                          => 'date ist erforderlich und muss ein gültiges Datum sein.',
        'invalidTimestamp'                     => 'Fehlender oder ungültiger Zeitstempel. Der Wert muss eine positive Zahl sein.',
        'appointmentNotAvailable'              => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.',
        'scopesNotFound'                       => 'Scope(s) nicht gefunden.',
        'servicesNotFound'                     => 'Für die angegebenen officeId(s) wurden keine Services gefunden.',
        'officesNotFound'                      => 'Für die angegebenen serviceId(s) wurden keine Standorte gefunden.',
        'noAppointmentForThisDay'              => 'Keine verfügbaren Termine für dieses Datum.',
        'noAppointmentForThisScope'            => 'An diesem Standort sind aktuell keine freien Termine verfügbar.',
        'invalidFamilyName'                    => 'familyName muss eine nicht-leere Zeichenkette sein.',
        'invalidEmail'                         => 'email muss eine gültige E-Mail-Adresse sein.',
        'invalidTelephone'                     => 'telephone muss eine Zahlenkette zwischen 7 und 15 Stellen sein.',
        'invalidCustomTextfield'               => 'customTextfield muss eine Zeichenkette sein.',
    ];
}