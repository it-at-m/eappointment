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
            'errorHeader' => 'The service is temporarily unavailable.',
            'errorMessage' => 'The service is temporarily unavailable. Please try again later.',
            'statusCode' => self::HTTP_UNAVAILABLE
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'errorHeader' => 'Feature not implemented yet.',
            'errorMessage' => 'Feature not implemented yet.',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorHeader' => 'Endpoint not found.',
            'errorMessage' => 'Endpoint not found.',
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid request.',
            'errorMessage' => 'Invalid request.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorHeader' => 'Request method not allowed.',
            'errorMessage' => 'Request method not allowed.',
        ],
        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Captcha verification failed.',
            'errorMessage' => 'Captcha verification failed.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid location and service combination.',
            'errorMessage' => 'The provided service(s) do not exist at the given location.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid start date.',
            'errorMessage' => 'startDate is required and must be a valid date.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid end date.',
            'errorMessage' => 'endDate is required and must be a valid date.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid office ID.',
            'errorMessage' => 'officeId should be a 32-bit integer.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid service ID.',
            'errorMessage' => 'serviceId should be a 32-bit integer.'
        ],
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Service IDs are Missing',
            'errorMessage' => 'Service IDs and counts cannot be empty'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Service IDs and counts must have same length',
            'errorMessage' => 'Service IDs and counts must have same length'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid service count.',
            'errorMessage' => 'serviceCounts should be an array of numeric values.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid process ID.',
            'errorMessage' => 'processId should be a positive 32-bit integer.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid scope ID.',
            'errorMessage' => 'scopeId should be a positive 32-bit integer.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid auth key.',
            'errorMessage' => 'authKey should be a string.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid date.',
            'errorMessage' => 'date is required and must be a valid date.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid timestamp.',
            'errorMessage' => 'Missing timestamp or invalid timestamp format. It should be a positive numeric value.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid family name.',
            'errorMessage' => 'familyName should be a non-empty string.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST, 
            'errorHeader' => 'Invalid email.',
            'errorMessage' => 'email should be a valid email address.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid telephone.',
            'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid custom textfield.',
            'errorMessage' => 'customTextfield should be a string.'
        ],
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorHeader' => 'Appointment can not be canceled.',
            'errorMessage' => 'The selected appointment cannot be canceled.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'Appointment is not available.',
            'errorMessage' => 'The selected appointment is unfortunately no longer available.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'No available days found.',
            'errorMessage' => 'No available days found for the given criteria.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Captcha verification error.',
            'errorMessage' => 'An error occurred during captcha verification.'
        ],
        'captchaMissing' => [
            'errorCode' => 'captchaMissing',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Missing captcha token.',
            'errorMessage' => 'Missing captcha token.',
        ],
        'captchaInvalid' => [
            'errorCode' => 'captchaInvalid',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid captcha token.',
            'errorMessage' => 'Invalid captcha token.',
        ],
        'captchaExpired' => [
            'errorCode' => 'captchaExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Captcha token expired.',
            'errorMessage' => 'Captcha token expired.',
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorHeader' => 'Service Unavailable.',
            'errorMessage' => 'Service Unavailable: The application is under maintenance.'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Invalid schema.',
            'errorMessage' => 'Data does not match the required schema.'
        ],

        //Zmsapi exceptions
        'internalError' => [
            'errorCode' => 'internalError',
            'errorHeader' => 'Internal error.',
            'errorMessage' => 'An internal error occurred. Please try again later.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',  
            'errorHeader' => 'Invalid API client.',
            'errorMessage' => 'Invalid API client.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'errorHeader' => 'Source not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Source not found.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorHeader' => 'Department not found.',
            'errorMessage' => 'Department not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorHeader' => 'Mail template not found.',
            'errorMessage' => 'Mail template not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorHeader' => 'Organisation not found.',
            'errorMessage' => 'Organisation not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorHeader' => 'Provider not found.',
            'errorMessage' => 'Provider not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorHeader' => 'Requested service not found.',
            'errorMessage' => 'Requested service not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound', 
            'errorHeader' => 'Scope not found.',
            'errorMessage' => 'Scope not found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorHeader' => 'Invalid process data.',
            'errorMessage' => 'The process data is invalid.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorHeader' => 'Appointment already exists.',
            'errorMessage' => 'An appointment process already exists.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorHeader' => 'Failed to delete the appointment.',
            'errorMessage' => 'Failed to delete the appointment.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorHeader' => 'Appointment already called.',
            'errorMessage' => 'The appointment has already been called.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorHeader' => 'Appointment is no longer reserved.',
            'errorMessage' => 'The appointment is no longer reserved.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorHeader' => 'Appointment is no longer preconfirmed.',
            'errorMessage' => 'The appointment is no longer preconfirmed.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorHeader' => 'Email address is required.',
            'errorMessage' => 'Email address is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorHeader' => 'Telephone number is required.',
            'errorMessage' => 'Telephone number is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorHeader' => 'We could not find your appointment.',
            'errorMessage' => 'Maybe you have already canceled your appointment? Otherwise, please check that you have used the correct link.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorHeader' => 'Invalid authentication key.',
            'errorMessage' => 'Invalid authentication key.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorHeader' => 'No appointments are currently available.',
            'errorMessage' => 'Please try again at a later time.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorHeader' => 'You have already booked too many appointments.',
            'errorMessage' => 'You can only book a limited number of appointments with your e-mail address. Please cancel another appointment before you book a new one.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorHeader' => 'No scopes found.',
            'errorMessage' => 'No scopes found.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Your Appointment can not be activated.',
            'errorMessage' => 'Unfortunately, the time for activating your appointment has expired. Please schedule the appointment again.',
        ],

        //Middleware exceptions
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Access denied',
            'errorMessage' => 'Access denied - IP address is blacklisted.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorHeader' => 'Rate limit exceeded.',
            'errorMessage' => 'Rate limit exceeded. Please try again later.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorHeader' => 'Request entity too large.',
            'errorMessage' => 'Request entity too large.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Security policy violation.',
            'errorMessage' => 'Security policy violation.'
        ]

    ];
// German messages
    public const DE = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorHeader' => 'Der Dienst ist vorübergehend nicht verfügbar.',
            'errorMessage' => 'Der Dienst ist vorübergehend nicht verfügbar. Bitte versuchen Sie es später erneut.',
            'statusCode' => self::HTTP_UNAVAILABLE
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorHeader' => 'Funktion ist noch nicht implementiert.',
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
            'errorHeader' => 'Ungültige Anfrage.',
            'errorMessage' => 'Ungültige Anfrage.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorHeader' => 'Anfragemethode nicht zulässig.',
            'errorMessage' => 'Anfragemethode nicht zulässig.',
        ],
        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Captcha-Prüfung fehlgeschlagen.',
            'errorMessage' => 'Captcha-Prüfung fehlgeschlagen.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Kombination von Standort und Dienstleistung.',
            'errorMessage' => 'Die angegebene Dienstleistung ist an diesem Standort nicht verfügbar.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiges Startdatum.',
            'errorMessage' => 'startDate ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiges Enddatum.',
            'errorMessage' => 'endDate ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Büro-ID.',
            'errorMessage' => 'officeId muss eine 32-Bit-Ganzzahl sein.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Dienstleistungs-ID.',
            'errorMessage' => 'serviceId muss eine 32-Bit-Ganzzahl sein.'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Dienstleistungsanzahl.',
            'errorMessage' => 'serviceCounts muss ein Array aus numerischen Werten sein.'
        ],
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Service-IDs und Anzahl dürfen nicht leer sein',
            'errorMessage' => 'Service-IDs und Anzahl dürfen nicht leer sein'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Service-IDs und Anzahl müssen gleiche Länge haben',
            'errorMessage' => 'Service-IDs und Anzahl müssen gleiche Länge haben'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Prozess-ID.',
            'errorMessage' => 'processId muss eine positive 32-Bit-Ganzzahl sein.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Bereichs-ID.',
            'errorMessage' => 'scopeId muss eine positive 32-Bit-Ganzzahl sein.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Authentifizierungsschlüssel.',
            'errorMessage' => 'authKey muss eine Zeichenkette sein.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiges Datum.',
            'errorMessage' => 'date ist erforderlich und muss ein gültiges Datum sein.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiger Zeitstempel.',
            'errorMessage' => 'Fehlender oder ungültiger Zeitstempel. Der Wert muss eine positive Zahl sein.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiger Familienname.',
            'errorMessage' => 'familyName muss eine nicht-leere Zeichenkette sein.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige E-Mail-Adresse.',
            'errorMessage' => 'email muss eine gültige E-Mail-Adresse sein.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige Telefonnummer.',
            'errorMessage' => 'telephone muss eine Zahlenkette zwischen 7 und 15 Stellen sein.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültige benutzerdefinierte Textfeld.',
            'errorMessage' => 'customTextfield muss eine Zeichenkette sein.'
        ],
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorHeader' => 'Termin kann nicht abgesagt werden.',
            'errorMessage' => 'Der von Ihnen gewählte Termin kann leider nicht mehr gelöscht werden.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'Ihr gewählter Termin ist nicht mehr verfügbar.',
            'errorMessage' => 'Leider hat inzwischen eine andere Person Ihren gewünschten Termin gebucht. Bitte wählen Sie einen neuen Termin aus.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'Keine verfügbaren Termine für dieses Datum.',
            'errorMessage' => 'Keine verfügbaren Termine für dieses Datum.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Captcha-Prüfung fehlgeschlagen.',
            'errorMessage' => 'Bei der Captcha-Prüfung ist ein Fehler aufgetreten.'
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorHeader' => 'Der Dienst ist nicht verfügbar.',
            'errorMessage' => 'Der Dienst ist nicht verfügbar: Die Anwendung wird gerade gewartet.'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ungültiges Schema.',
            'errorMessage' => 'Daten entsprechen nicht dem erforderlichen Schema.'
        ],

        //Zmsapi exceptions
        'internalError' => [
            'errorCode' => 'internalError',
            'errorHeader' => 'Ein interner Fehler ist aufgetreten.',
            'errorMessage' => 'Ein interner Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorHeader' => 'Ungültiger API-Client.',
            'errorMessage' => 'Ungültiger API-Client.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorHeader' => 'Quelle nicht gefunden.',
            'errorMessage' => 'Quelle nicht gefunden.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorHeader' => 'Abteilung nicht gefunden.',
            'errorMessage' => 'Abteilung nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorHeader' => 'E-Mail-Vorlage nicht gefunden.',
            'errorMessage' => 'E-Mail-Vorlage nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorHeader' => 'Organisation nicht gefunden.',
            'errorMessage' => 'Organisation nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorHeader' => 'Anbieter nicht gefunden.',
            'errorMessage' => 'Anbieter nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorHeader' => 'Angeforderter Dienst nicht gefunden.',
            'errorMessage' => 'Angeforderter Dienst nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorHeader' => 'Bereich nicht gefunden.',
            'errorMessage' => 'Bereich nicht gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorHeader' => 'Ungültige Prozessdaten.',
            'errorMessage' => 'Die Prozessdaten sind ungültig.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorHeader' => 'Terminprozess existiert bereits.',
            'errorMessage' => 'Ein Terminprozess existiert bereits.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorHeader' => 'Termin nicht löschbar.',
            'errorMessage' => 'Der Termin konnte nicht gelöscht werden.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorHeader' => 'Termin bereits aufgerufen.',
            'errorMessage' => 'Der Termin wurde bereits aufgerufen.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorHeader' => 'Termin nicht mehr reserviert.',
            'errorMessage' => 'Der Termin ist nicht mehr reserviert.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorHeader' => 'Termin nicht mehr vorbestätigt.',
            'errorMessage' => 'Der Termin ist nicht mehr vorbestätigt.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorHeader' => 'E-Mail-Adresse erforderlich.',
            'errorMessage' => 'E-Mail-Adresse ist erforderlich.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorHeader' => 'Telefonnummer erforderlich.',
            'errorMessage' => 'Telefonnummer ist erforderlich.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorHeader' => 'Wir konnten Ihren Termin nicht finden.',
            'errorMessage' => 'Vielleicht haben Sie Ihren Termin bereits abgesagt? Andernfalls überprüfen Sie bitte, ob Sie den richtigen Link verwendet haben.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorHeader' => 'Ungültiger Authentifizierungsschlüssel.',
            'errorMessage' => 'Ungültiger Authentifizierungsschlüssel.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorHeader' => 'Aktuell ist kein Termin verfügbar.',
            'errorMessage' => 'Bitte versuchen Sie es noch einmal zu einem späteren Zeitpunkt.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorHeader' => 'Zu viele Termine mit derselben E-Mail-Adresse.',
            'errorMessage' => 'Sie können mit Ihrer E-Mail-Adresse nur eine begrenzte Anzahl an Terminen vereinbaren. Bitte sagen Sie einen anderen Termin ab, bevor Sie einen neuen Termin buchen.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorHeader' => 'Keine Bereiche gefunden.',
            'errorMessage' => 'Keine Bereiche gefunden.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ihr Termin kann nicht mehr aktiviert werden.',
            'errorMessage' => 'Leider ist die Zeit für die Aktivierung Ihres Termins abgelaufen. Bitte vereinbaren Sie den Termin erneut.',
        ],

        //Middleware exceptions
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Zugriff verweigert',
            'errorMessage' => 'Zugriff verweigert - IP-Adresse ist auf der schwarzen Liste.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorHeader' => 'Anfragelimit überschritten.',
            'errorMessage' => 'Anfragelimit überschritten. Bitte versuchen Sie es später erneut.'  // DE: 'Anfragelimit überschritten. Bitte versuchen Sie es später erneut.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorHeader' => 'Anfrage zu groß.',
            'errorMessage' => 'Anfrage zu groß.'  // DE: 'Anfrage zu groß.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Verstoß gegen Sicherheitsrichtlinien.',
            'errorMessage' => 'Verstoß gegen Sicherheitsrichtlinien.'  // DE: 'Verstoß gegen Sicherheitsrichtlinien.'
        ]

    ];
    public const UA = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorHeader' => 'Сервіс тимчасово недоступний.',
            'errorMessage' => 'Сервіс тимчасово недоступний. Будь ласка, спробуйте пізніше.',
            'statusCode' => self::HTTP_UNAVAILABLE
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorHeader' => 'Функція ще не реалізована.',
            'errorMessage' => 'Функцію ще не реалізовано.',
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,   
            'errorHeader' => 'Кінцева точка не знайдена.',
            'errorMessage' => 'Кінцеву точку не знайдено.',
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Недійсний запит.',
            'errorMessage' => 'Недійсний запит.'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorHeader' => 'Метод запиту не дозволено.',
            'errorMessage' => 'Метод запиту не дозволено.',
        ],
        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Перевірка капчі не вдалася.',
            'errorMessage' => 'Перевірка капчі не вдалася.'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Недійсна комбінація місця та послуги.',
            'errorMessage' => 'Вказані послуги не існують у цьому місці.'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST, 
            'errorHeader' => 'Недійсна дата початку.',
            'errorMessage' => 'Потрібна дійсна дата початку.'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Недійсна дата завершення.',
            'errorMessage' => 'Потрібна дійсна дата завершення.'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Недійсна офісна ідентифікація.',
            'errorMessage' => 'officeId має бути 32-бітним цілим числом.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Недійсна послуга.',
            'errorMessage' => 'serviceId має бути 32-бітним цілим числом.'
        ],
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ідентифікатори та кількість послуг не можуть бути порожніми',
            'errorMessage' => 'Ідентифікатори та кількість послуг не можуть бути порожніми'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ідентифікатори та кількість послуг повинні мати однакову довжину',
            'errorMessage' => 'Ідентифікатори та кількість послуг повинні мати однакову довжину'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'serviceCounts має бути масивом числових значень.',
            'errorMessage' => 'serviceCounts має бути масивом числових значень.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'processId має бути додатним 32-бітним цілим числом.',
            'errorMessage' => 'processId має бути додатним 32-бітним цілим числом.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'scopeId має бути додатним 32-бітним цілим числом.',
            'errorMessage' => 'scopeId має бути додатним 32-бітним цілим числом.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'authKey має бути рядком.',
            'errorMessage' => 'authKey має бути рядком.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Потрібна дійсна дата.',
            'errorMessage' => 'Потрібна дійсна дата.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Відсутня або недійсна мітка часу. Має бути додатним числовим значенням.',
            'errorMessage' => 'Відсутня або недійсна мітка часу. Має бути додатним числовим значенням.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Прізвище не може бути порожнім.',
            'errorMessage' => 'Прізвище не може бути порожнім.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Потрібна дійсна електронна адреса.',
            'errorMessage' => 'Потрібна дійсна електронна адреса.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Номер телефону має містити від 7 до 15 цифр.',
            'errorMessage' => 'Номер телефону має містити від 7 до 15 цифр.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'customTextfield має бути рядком.',
            'errorMessage' => 'customTextfield має бути рядком.'
        ],
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorHeader' => 'Обраний запис не може бути скасовано.',
            'errorMessage' => 'Обраний запис не може бути скасовано.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'На жаль, обраний запис більше недоступний.',
            'errorMessage' => 'На жаль, обраний запис більше недоступний.'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_OK,
            'errorHeader' => 'Немає доступних днів за вказаними критеріями.',
            'errorMessage' => 'Немає доступних днів за вказаними критеріями.'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Виникла помилка під час перевірки капчі.',
            'errorMessage' => 'Виникла помилка під час перевірки капчі.'
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorHeader' => 'Сервіс недоступний: Додаток перебуває на технічному обслуговуванні.',
            'errorMessage' => 'Сервіс недоступний: Додаток перебуває на технічному обслуговуванні.'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Дані не відповідають необхідній схемі.',
            'errorMessage' => 'Дані не відповідають необхідній схемі.'
        ],
        'internalError' => [
            'errorCode' => 'internalError',
            'errorHeader' => 'Виникла внутрішня помилка.',
            'errorMessage' => 'Виникла внутрішня помилка. Спробуйте пізніше.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorHeader' => 'Недійсний API клієнт.',
            'errorMessage' => 'Недійсний API клієнт.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorHeader' => 'Джерело не знайдено.',
            'errorMessage' => 'Джерело не знайдено.',
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorHeader' => 'Відділ не знайдено.',
            'errorMessage' => 'Відділ не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorHeader' => 'Шаблон листа не знайдено.',
            'errorMessage' => 'Шаблон листа не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorHeader' => 'Організацію не знайдено.',
            'errorMessage' => 'Організацію не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorHeader' => 'Постачальника не знайдено.',
            'errorMessage' => 'Постачальника не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorHeader' => 'Запитану послугу не знайдено.',
            'errorMessage' => 'Запитану послугу не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorHeader' => 'Область не знайдено.',
            'errorMessage' => 'Область не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorHeader' => 'Дані процесу недійсні.',
            'errorMessage' => 'Дані процесу недійсні.',
            'statusCode' => self::HTTP_BAD_REQUEST
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorHeader' => 'Процес запису вже існує.',
            'errorMessage' => 'Процес запису вже існує.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorHeader' => 'Не вдалося видалити запис.',
            'errorMessage' => 'Не вдалося видалити запис.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorHeader' => 'Запис вже викликано.',
            'errorMessage' => 'Запис вже викликано.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorHeader' => 'Запис більше не зарезервовано.',
            'errorMessage' => 'Запис більше не зарезервовано.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorHeader' => 'Запис більше не попередньо підтверджено.',
            'errorMessage' => 'Запис більше не попередньо підтверджено.',
            'statusCode' => self::HTTP_CONFLICT
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorHeader' => 'Потрібна електронна адреса.',
            'errorMessage' => 'Потрібна електронна адреса.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorHeader' => 'Потрібен номер телефону.',
            'errorMessage' => 'Потрібен номер телефону.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorHeader' => 'Ми не можемо знайти ваш запис.',
            'errorMessage' => 'Можливо, ви вже скасували свій запис? Інакше перевірте, чи ви використали правильне посилання.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorHeader' => 'Недійсний ключ автентифікації.',
            'errorMessage' => 'Недійсний ключ автентифікації.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorHeader' => 'Наразі на цій локації немає вільних записів.',
            'errorMessage' => 'Будь ласка, спробуйте знову пізніше.',
            'statusCode' => self::HTTP_OK
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorHeader' => 'Ви вже забронювали занадто багато зустрічей.',
            'errorMessage' => 'Ви можете забронювати лише обмежену кількість зустрічей за допомогою вашої електронної адреси. Будь ласка, скасуйте попередню зустріч, перш ніж забронювати нову.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorHeader' => 'Області не знайдено.',
            'errorMessage' => 'Області не знайдено.',
            'statusCode' => self::HTTP_NOT_FOUND
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorHeader' => 'Ваш термін не може бути активований.',
            'errorMessage' => 'На жаль, час для активації вашого терміну закінчився. Будь ласка, домовтеся про новий термін.',
        ],
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Доступ заборонено - IP-адресу заблоковано.',
            'errorMessage' => 'Доступ заборонено - IP-адресу заблоковано.'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorHeader' => 'Перевищено ліміт запитів. Спробуйте пізніше.',
            'errorMessage' => 'Перевищено ліміт запитів. Спробуйте пізніше.'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorHeader' => 'Тіло запиту занадто велике.',
            'errorMessage' => 'Тіло запиту занадто велике.'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorHeader' => 'Порушення політики безпеки.',
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
                'errorHeader' => 'Ein unbekannter Fehler ist aufgetreten.',
                'errorMessage' => 'Ein unbekannter Fehler ist aufgetreten.'
            ],
            'ua' => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorHeader' => 'Виникла невідома помилка.',
                'errorMessage' => 'Виникла невідома помилка.'
            ],
            default => [
                'errorCode' => 'unknownError',
                'statusCode' => self::HTTP_UNKNOWN,
                'errorHeader' => 'An unknown error occurred.',
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
