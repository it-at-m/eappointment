<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Localization;

class ErrorMessages
{
    private const HTTP_OK = 200;

    private const HTTP_BAD_REQUEST = 400;

    private const HTTP_NOT_FOUND = 404;

    private const HTTP_NOT_ACCEPTABLE_CLIENT = 406;

    private const HTTP_INTERNAL_SERVER_ERROR = 500;

    private const HTTP_UNAVAILABLE = 503;

    private const HTTP_UNKNOWN = 520;

    private const DEFAULT_LANGUAGE = 'DE';
    private const FALLBACK_LANGUAGE = 'EN';

    // English messages
    public const EN = [

        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Captcha verification failed.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'The provided service(s) do not exist at the given location.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'startDate is required and must be a valid date.'
        ],
        'invalidStartDateFormat' => [
            'errorCode' => 'invalidStartDateFormat',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'startDate must be in YYYY-MM-DD format.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate is required and must be a valid date.'
        ],
        'invalidEndDateFormat' => [
            'errorCode' => 'invalidEndDateFormat',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate must be in YYYY-MM-DD format.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'officeId should be a 32-bit integer.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceId should be a 32-bit integer.'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceCounts should be an array of numeric values.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'processId should be a positive 32-bit integer.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'scopeId should be a positive 32-bit integer.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'authKey should be a string.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'date is required and must be a valid date.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Missing timestamp or invalid timestamp format. It should be a positive numeric value.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'familyName should be a non-empty string.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'email should be a valid email address.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'customTextfield should be a string.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'The selected appointment is unfortunately no longer available.'
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Scope(s) not found.'
        ],
        'servicesNotFound' => [
            'errorCode' => 'servicesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Service(s) not found for the provided officeId(s).'
        ],
        'officesNotFound' => [
            'errorCode' => 'officesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Office(s) not found for the provided serviceId(s).'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'No available days found for the given criteria.'
        ],
        'noAppointmentsAtLocation' => [
            'errorCode' => 'noAppointmentsAtLocation',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'There are currently no free appointments available at this location.'
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Appointment not found.'
        ],
        'authKeyMissMatch' => [
            'errorCode' => 'authKeyMissMatch',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE_CLIENT,
            'errorMessage' => 'authKey is not correct for the appointment.'
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE_CLIENT,
            'errorMessage' => 'Too many appointments with the same e-mail address.'
        ],
        'internalError' => [
            'errorCode' => 'internalError',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR,
            'errorMessage' => 'An internal error occurred.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'An error occurred during captcha verification.'
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorMessage' => 'Service Unavailable: The application is under maintenance.'
        ]

    ];

    // German messages
    public const DE = [

        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Captcha-Prüfung fehlgeschlagen.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Die angegebene Dienstleistung ist an diesem Standort nicht verfügbar.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'startDate ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidStartDateFormat' => [
            'errorCode' => 'invalidStartDateFormat',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'startDate muss im Format YYYY-MM-DD vorliegen.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidEndDateFormat' => [
            'errorCode' => 'invalidEndDateFormat',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate muss im Format YYYY-MM-DD vorliegen.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'officeId muss eine 32-Bit-Ganzzahl sein.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceId muss eine 32-Bit-Ganzzahl sein.'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceCounts muss ein Array aus numerischen Werten sein.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'processId muss eine positive 32-Bit-Ganzzahl sein.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'scopeId muss eine positive 32-Bit-Ganzzahl sein.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'authKey muss eine Zeichenkette sein.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'date ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Fehlender oder ungültiger Zeitstempel. Der Wert muss eine positive Zahl sein.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'familyName muss eine nicht-leere Zeichenkette sein.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'email muss eine gültige E-Mail-Adresse sein.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'telephone muss eine Zahlenkette zwischen 7 und 15 Stellen sein.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'customTextfield muss eine Zeichenkette sein.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.'
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Scope(s) nicht gefunden.'
        ],
        'servicesNotFound' => [
            'errorCode' => 'servicesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Für die angegebenen officeId(s) wurden keine Services gefunden.'
        ],
        'officesNotFound' => [
            'errorCode' => 'officesNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Für die angegebenen serviceId(s) wurden keine Standorte gefunden.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Keine verfügbaren Termine für dieses Datum.'
        ],
        'noAppointmentsAtLocation' => [
            'errorCode' => 'noAppointmentsAtLocation',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'An diesem Standort sind aktuell keine freien Termine verfügbar.'
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Termin wurde nicht gefunden.'
        ],
        'authKeyMissMatch' => [
            'errorCode' => 'authKeyMissMatch',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE_CLIENT,
            'errorMessage' => 'authKey ist nicht korrekt für den Termin.'
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE_CLIENT,
            'errorMessage' => 'Zu viele Termine mit gleicher E-Mail- Adresse.'
        ],
        'internalError' => [
            'errorCode' => 'internalError',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR,
            'errorMessage' => 'Es ist ein interner Fehler aufgetreten.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Bei der Captcha-Prüfung ist ein Fehler aufgetreten.'
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorMessage' => 'Der Dienst ist nicht verfügbar: Die Anwendung wird gerade gewartet.'
        ]

    ];

    /**
     * Get an error message by key with fallback logic.
     *
     * @param string $key The error message key.
     * @param string|null $language Optional language (default is centralized DEFAULT_LANGUAGE).
     * @return array The error message array.
     */
    public static function get(string $key, ?string $language = null): array
    {
        $language = $language ?? self::DEFAULT_LANGUAGE;
    
        // Attempt to get messages for the specified language
        $messages = match ($language) {
            'DE' => self::DE,
            'EN' => self::EN,
            default => self::EN,
        };
    
        if (isset($messages[$key])) {
            return $messages[$key];
        }

        $fallbackMessages = match (self::FALLBACK_LANGUAGE) {
            'DE' => self::DE,
            'EN' => self::EN,
            default => self::EN,
        };
    
        if (isset($fallbackMessages[$key])) {
            return $fallbackMessages[$key];
        }
    
        $genericErrorMessage = match ($language) {
            'DE' => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorMessage' => 'Ein unbekannter Fehler ist aufgetreten.'
            ],
            default => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorMessage' => 'An unknown error occurred.'
            ],
        };

        return $genericErrorMessage;
    }
    
    public static function getHighestStatusCode(array $errors): int
    {
        if (empty($errors)) {
               return self::HTTP_OK;
        }
        $errorCodes = array_column($errors, 'statusCode');
        return max($errorCodes);
    }

}
