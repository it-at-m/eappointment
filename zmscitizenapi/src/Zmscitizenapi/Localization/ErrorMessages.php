<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Localization;

use BO\Zmscitizenapi\Middleware\LanguageMiddleware;

class ErrorMessages
{
    private const HTTP_OK = 200;
    private const HTTP_BAD_REQUEST = 400;
    private const HTTP_FORBIDDEN = 403;
    private const HTTP_NOT_FOUND = 404;
    private const HTTP_INVALID_REQUEST_METHOD = 405;
    private const HTTP_NOT_ACCEPTABLE = 406;
    private const HTTP_CONFLICT = 409;
    private const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    private const HTTP_TOO_MANY_REQUESTS = 429;
    private const HTTP_INTERNAL_SERVER_ERROR = 500;
    private const HTTP_NOT_IMPLEMENTED = 501;
    private const HTTP_UNAVAILABLE = 503;
    private const HTTP_UNKNOWN = 520;
    private const DEFAULT_LANGUAGE = 'DE';
    private const FALLBACK_LANGUAGE = 'EN';
// English messages
    public const EN = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorMessage' => 'The service is temporarily unavailable. Please try again later.',
            'statusCode' => 503
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorMessage' => 'Feature not implemented yet.',
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Endpoint not found.',
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Invalid request.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorMessage' => 'Request method not allowed.',
        ],
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
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate is required and must be a valid date.'
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
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service IDs and counts cannot be empty'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service IDs and counts must have same length'
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
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorMessage' => 'The selected appointment cannot be canceled.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'The selected appointment is unfortunately no longer available.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'No available days found for the given criteria.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'An error occurred during captcha verification.'
        ],
        'captchaMissing' => [
            'errorCode' => 'captchaMissing',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Missing captcha token.',
        ],
        'captchaInvalid' => [
            'errorCode' => 'captchaInvalid',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Invalid captcha token.',
        ],
        'captchaExpired' => [
            'errorCode' => 'captchaExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Captcha token expired.',
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorMessage' => 'Service Unavailable: The application is under maintenance.'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Data does not match the required schema.'
        ],

        //Zmsapi exceptions
        'internalError' => [
            'errorCode' => 'internalError',
            'errorMessage' => 'An internal error occurred. Please try again later.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorMessage' => 'Invalid API client.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Source not found.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorMessage' => 'Department not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorMessage' => 'Mail template not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorMessage' => 'Organisation not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorMessage' => 'Provider not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorMessage' => 'Requested service not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorMessage' => 'Scope not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorMessage' => 'The process data is invalid.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorMessage' => 'An appointment process already exists.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorMessage' => 'Failed to delete the appointment.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorMessage' => 'The appointment has already been called.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorMessage' => 'The appointment is no longer reserved.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorMessage' => 'The appointment is no longer preconfirmed.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorMessage' => 'Email address is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorMessage' => 'Telephone number is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorMessage' => 'The requested appointment could not be found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorMessage' => 'Invalid authentication key.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'Unfortunately, there are currently no available appointments at this location.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorMessage' => 'Too many appointments with the same email address.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorMessage' => 'No scopes found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'The preconfirmation has expired. Please make a new appointment.'
        ],

        //Middleware exceptions
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Access denied - IP address is blacklisted.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorMessage' => 'Rate limit exceeded. Please try again later.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorMessage' => 'Request entity too large.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Security policy violation.'
        ]

    ];
// German messages
    public const DE = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorMessage' => 'Der Dienst ist vorübergehend nicht verfügbar. Bitte versuchen Sie es später erneut.',
            'statusCode' => 503
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorMessage' => 'Funktion ist noch nicht implementiert.',
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Endpunkt nicht gefunden.',
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Ungültige Anfrage.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorMessage' => 'Anfragemethode nicht zulässig.',
        ],
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
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate ist erforderlich und muss ein gültiges Datum sein.'
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
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service-IDs und Anzahl dürfen nicht leer sein'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service-IDs und Anzahl müssen gleiche Länge haben'
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
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorMessage' => 'Der von Ihnen gewählte Termin kann leider nicht mehr gelöscht werden.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'Der von Ihnen gewählte Termin ist leider nicht mehr verfügbar.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'Keine verfügbaren Termine für dieses Datum.'
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
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Daten entsprechen nicht dem erforderlichen Schema.'
        ],

        //Zmsapi exceptions
        'internalError' => [
            'errorCode' => 'internalError',
            'errorMessage' => 'Ein interner Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorMessage' => 'Ungültiger API-Client.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Quelle nicht gefunden.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorMessage' => 'Abteilung nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorMessage' => 'E-Mail-Vorlage nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorMessage' => 'Organisation nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorMessage' => 'Anbieter nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorMessage' => 'Angeforderter Dienst nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorMessage' => 'Bereich nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorMessage' => 'Die Prozessdaten sind ungültig.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorMessage' => 'Ein Terminprozess existiert bereits.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorMessage' => 'Der Termin konnte nicht gelöscht werden.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorMessage' => 'Der Termin wurde bereits aufgerufen.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorMessage' => 'Der Termin ist nicht mehr reserviert.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorMessage' => 'Der Termin ist nicht mehr vorbestätigt.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorMessage' => 'E-Mail-Adresse ist erforderlich.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorMessage' => 'Telefonnummer ist erforderlich.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorMessage' => 'Der angeforderte Termin wurde nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorMessage' => 'Ungültiger Authentifizierungsschlüssel.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'An diesem Standort gibt es aktuell leider keine freien Termine.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorMessage' => 'Zu viele Termine mit derselben E-Mail-Adresse.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorMessage' => 'Keine Bereiche gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Die Vorbestätigung ist abgelaufen. Bitte vereinbaren Sie einen neuen Termin.'
        ],

        //Middleware exceptions
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Zugriff verweigert - IP-Adresse ist auf der schwarzen Liste.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorMessage' => 'Anfragelimit überschritten. Bitte versuchen Sie es später erneut.'  // DE: 'Anfragelimit überschritten. Bitte versuchen Sie es später erneut.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorMessage' => 'Anfrage zu groß.'  // DE: 'Anfrage zu groß.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Verstoß gegen Sicherheitsrichtlinien.'  // DE: 'Verstoß gegen Sicherheitsrichtlinien.'
        ]

    ];
    public const UA = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorMessage' => 'Сервіс тимчасово недоступний. Будь ласка, спробуйте пізніше.',
            'statusCode' => 503
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorMessage' => 'Функцію ще не реалізовано.',
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Кінцеву точку не знайдено.',
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Недійсний запит.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorMessage' => 'Метод запиту не дозволено.',
        ],
        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Перевірка капчі не вдалася.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Вказані послуги не існують у цьому місці.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Потрібна дійсна дата початку.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Потрібна дійсна дата завершення.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'officeId має бути 32-бітним цілим числом.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceId має бути 32-бітним цілим числом.'
        ],
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Ідентифікатори та кількість послуг не можуть бути порожніми'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Ідентифікатори та кількість послуг повинні мати однакову довжину'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'serviceCounts має бути масивом числових значень.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'processId має бути додатним 32-бітним цілим числом.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'scopeId має бути додатним 32-бітним цілим числом.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'authKey має бути рядком.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Потрібна дійсна дата.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Відсутня або недійсна мітка часу. Має бути додатним числовим значенням.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Прізвище не може бути порожнім.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Потрібна дійсна електронна адреса.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Номер телефону має містити від 7 до 15 цифр.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'customTextfield має бути рядком.'
        ],
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorMessage' => 'Обраний запис не може бути скасовано.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'На жаль, обраний запис більше недоступний.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorMessage' => 'Немає доступних днів за вказаними критеріями.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Виникла помилка під час перевірки капчі.'
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorMessage' => 'Сервіс недоступний: Додаток перебуває на технічному обслуговуванні.'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Дані не відповідають необхідній схемі.'
        ],
        'internalError' => [
            'errorCode' => 'internalError',
            'errorMessage' => 'Виникла внутрішня помилка. Спробуйте пізніше.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorMessage' => 'Недійсний API клієнт.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Джерело не знайдено.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorMessage' => 'Відділ не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorMessage' => 'Шаблон листа не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorMessage' => 'Організацію не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorMessage' => 'Постачальника не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorMessage' => 'Запитану послугу не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorMessage' => 'Область не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorMessage' => 'Дані процесу недійсні.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorMessage' => 'Процес запису вже існує.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorMessage' => 'Не вдалося видалити запис.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorMessage' => 'Запис вже викликано.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorMessage' => 'Запис більше не зарезервовано.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorMessage' => 'Запис більше не попередньо підтверджено.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorMessage' => 'Потрібна електронна адреса.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorMessage' => 'Потрібен номер телефону.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorMessage' => 'Запитаний запис не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorMessage' => 'Недійсний ключ автентифікації.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'На жаль, наразі на цій локації немає вільних записів.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorMessage' => 'Забагато записів з однією електронною адресою.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorMessage' => 'Області не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Попереднє підтвердження закінчилося. Будь ласка, створіть новий запис.'
        ],
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Доступ заборонено - IP-адресу заблоковано.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorMessage' => 'Перевищено ліміт запитів. Спробуйте пізніше.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorMessage' => 'Тіло запиту занадто велике.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Порушення політики безпеки.'
        ]
    ];
/**
     * Get an error message by key with fallback logic.
     *
     * @param string $key The error message key.
     * @param string|null $language Optional language (default is LanguageMiddleware's default).
     * @return array The error message array.
     */
    public static function get(string $key, ?string $language = null): array
    {
        $language = LanguageMiddleware::normalizeLanguage($language);
        $messages = match ($language) {
            'en' => self::EN,
            'de' => self::DE,
            'ua' => self::UA,
            default => constant('self::' . strtoupper(LanguageMiddleware::getDefaultLanguage())),
        };
        if (isset($messages[$key])) {
            return $messages[$key];
        }

        $fallbackMessages = constant('self::' . strtoupper(LanguageMiddleware::getFallbackLanguage()));
        if (isset($fallbackMessages[$key])) {
            return $fallbackMessages[$key];
        }

        return match ($language) {
            'de' => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorMessage' => 'Ein unbekannter Fehler ist aufgetreten.'
            ],
            'ua' => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorMessage' => 'Виникла невідома помилка.'
            ],
            default => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorMessage' => 'An unknown error occurred.'
            ],
        };
    }

    /**
     * Get the highest status code from an array of errors.
     *
     * @param array $errors Array of error messages
     * @return int The highest status code found, or HTTP_OK (200) if no errors
     * @throws \InvalidArgumentException If any error has an invalid structure
     */
    public static function getHighestStatusCode(array $errors): int
    {
        if (empty($errors)) {
            return self::HTTP_OK;
        }

        $errorCodes = [];
        foreach ($errors as $error) {
            if (!is_array($error) || !isset($error['statusCode']) || !is_int($error['statusCode'])) {
                throw new \InvalidArgumentException('Invalid error structure. Each error must have a statusCode.');
            }
            $errorCodes[] = $error['statusCode'];
        }

        return max($errorCodes);
    }
}
