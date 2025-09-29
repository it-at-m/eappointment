<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Utils;

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

    // English messages only
    private const MESSAGES = [
        'zmsClientCommunicationError' => [
            'errorCode' => 'zmsClientCommunicationError',
            'errorMessage' => 'The service is temporarily unavailable. Please try again later.',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorType' => 'error'
        ],
        'notImplemented' => [
            'errorCode' => 'notImplemented',
            'errorMessage' => 'Feature not implemented yet.',
            'statusCode' => self::HTTP_NOT_IMPLEMENTED,
            'errorType' => 'error'
        ],
        'notFound' => [
            'errorCode' => 'notFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Endpoint not found.',
            'errorType' => 'error'
        ],
        'invalidRequest' => [
            'errorCode' => 'invalidRequest',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Invalid request.',
            'errorType' => 'warning'
        ],
        'requestMethodNotAllowed' => [
            'errorCode' => 'requestMethodNotAllowed',
            'statusCode' => self::HTTP_INVALID_REQUEST_METHOD,
            'errorMessage' => 'Request method not allowed.',
            'errorType' => 'warning'
        ],
        'captchaVerificationFailed' => [
            'errorCode' => 'captchaVerificationFailed',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Captcha verification failed.',
            'errorType' => 'warning'
        ],
        'invalidLocationAndServiceCombination' => [
            'errorCode' => 'invalidLocationAndServiceCombination',
            'errorMessage' => 'The provided service(s) do not exist at the given location.',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning'
        ],
        'invalidStartDate' => [
            'errorCode' => 'invalidStartDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'startDate is required and must be a valid date.',
            'errorType' => 'warning'
        ],
        'invalidEndDate' => [
            'errorCode' => 'invalidEndDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'endDate is required and must be a valid date.',
            'errorType' => 'warning'
        ],
        'invalidOfficeId' => [
            'errorCode' => 'invalidOfficeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'officeId should be a 32-bit integer.'
        ],
        'invalidServiceId' => [
            'errorCode' => 'invalidServiceId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'serviceId should be a 32-bit integer.'
        ],
        'emptyServiceArrays' => [
            'errorCode' => 'EMPTY_SERVICE_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service IDs and counts cannot be empty.',
            'errorType' => 'warning'
        ],
        'mismatchedArrays' => [
            'errorCode' => 'MISMATCHED_ARRAYS',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Service IDs and counts must have same length.',
            'errorType' => 'warning'
        ],
        'invalidServiceCount' => [
            'errorCode' => 'invalidServiceCount',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'serviceCounts should be an array of numeric values.'
        ],
        'invalidProcessId' => [
            'errorCode' => 'invalidProcessId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'processId should be a positive 32-bit integer.'
        ],
        'invalidScopeId' => [
            'errorCode' => 'invalidScopeId',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'scopeId should be a positive 32-bit integer.'
        ],
        'invalidAuthKey' => [
            'errorCode' => 'invalidAuthKey',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'authKey should be a string.'
        ],
        'invalidDate' => [
            'errorCode' => 'invalidDate',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'date is required and must be a valid date.'
        ],
        'invalidTimestamp' => [
            'errorCode' => 'invalidTimestamp',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'Missing timestamp or invalid timestamp format. It should be a positive numeric value.'
        ],
        'invalidFamilyName' => [
            'errorCode' => 'invalidFamilyName',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'familyName should be a non-empty string.'
        ],
        'invalidEmail' => [
            'errorCode' => 'invalidEmail',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'email should be a valid email address.'
        ],
        'invalidTelephone' => [
            'errorCode' => 'invalidTelephone',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'telephone should be a numeric string between 7 and 15 digits.'
        ],
        'invalidCustomTextfield' => [
            'errorCode' => 'invalidCustomTextfield',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'customTextfield should be a string.'
        ],
        'appointmentCanNotBeCanceled' => [
            'errorCode' => 'appointmentCanNotBeCanceled',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorType' => 'warning',
            'errorMessage' => 'The selected appointment cannot be canceled.'
        ],
        'appointmentNotAvailable' => [
            'errorCode' => 'appointmentNotAvailable',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'The selected appointment is unfortunately no longer available.',
            'errorType' => 'error'
        ],
        'noAppointmentForThisDay' => [
            'errorCode' => 'noAppointmentForThisDay',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'No available days found for the given criteria.',
            'errorType' => 'info'
        ],
        'captchaVerificationError' => [
            'errorCode' => 'captchaVerificationError',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'An error occurred during captcha verification.'
        ],
        'captchaMissing' => [
            'errorCode' => 'captchaMissing',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'Missing captcha token.',
        ],
        'captchaInvalid' => [
            'errorCode' => 'captchaInvalid',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'Invalid captcha token.',
        ],
        'captchaExpired' => [
            'errorCode' => 'captchaExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'Captcha token expired.',
        ],
        'serviceUnavailable' => [
            'errorCode' => 'serviceUnavailable',
            'statusCode' => self::HTTP_UNAVAILABLE,
            'errorMessage' => 'Service Unavailable: The application is under maintenance.',
            'errorType' => 'error'
        ],
        'invalidSchema' => [
            'errorCode' => 'invalidSchema',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning',
            'errorMessage' => 'Data does not match the required schema.'
        ],

        //Zmsapi exceptions
        'internalError' => [
            'errorCode' => 'internalError',
            'errorMessage' => 'An internal error occurred. Please try again later.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR,
            'errorType' => 'error'
        ],
        'invalidApiClient' => [
            'errorCode' => 'invalidApiClient',
            'errorMessage' => 'Invalid API client.',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning'
        ],
        'sourceNotFound' => [
            'errorCode' => 'sourceNotFound',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorMessage' => 'Source not found.',
            'errorType' => 'error'
        ],
        'departmentNotFound' => [
            'errorCode' => 'departmentNotFound',
            'errorMessage' => 'Department not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'mailNotFound' => [
            'errorCode' => 'mailNotFound',
            'errorMessage' => 'Mail template not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'organisationNotFound' => [
            'errorCode' => 'organisationNotFound',
            'errorMessage' => 'Organisation not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'providerNotFound' => [
            'errorCode' => 'providerNotFound',
            'errorMessage' => 'Provider not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'requestNotFound' => [
            'errorCode' => 'requestNotFound',
            'errorMessage' => 'Requested service not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'scopeNotFound' => [
            'errorCode' => 'scopeNotFound',
            'errorMessage' => 'Scope not found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'processInvalid' => [
            'errorCode' => 'processInvalid',
            'errorMessage' => 'The process data is invalid.',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorType' => 'warning'
        ],
        'processAlreadyExists' => [
            'errorCode' => 'processAlreadyExists',
            'errorMessage' => 'An appointment process already exists.',
            'statusCode' => self::HTTP_CONFLICT,
            'errorType' => 'warning'
        ],
        'processDeleteFailed' => [
            'errorCode' => 'processDeleteFailed',
            'errorMessage' => 'Failed to delete the appointment.',
            'statusCode' => self::HTTP_INTERNAL_SERVER_ERROR,
            'errorType' => 'error'
        ],
        'processAlreadyCalled' => [
            'errorCode' => 'processAlreadyCalled',
            'errorMessage' => 'The appointment has already been called.',
            'statusCode' => self::HTTP_CONFLICT,
            'errorType' => 'warning'
        ],
        'processNotReservedAnymore' => [
            'errorCode' => 'processNotReservedAnymore',
            'errorMessage' => 'The appointment is no longer reserved.',
            'statusCode' => self::HTTP_CONFLICT,
            'errorType' => 'error'
        ],
        'processNotPreconfirmedAnymore' => [
            'errorCode' => 'processNotPreconfirmedAnymore',
            'errorMessage' => 'The appointment is no longer preconfirmed.',
            'statusCode' => self::HTTP_CONFLICT,
            'errorType' => 'error'
        ],
        'emailIsRequired' => [
            'errorCode' => 'emailIsRequired',
            'errorMessage' => 'Email address is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorType' => 'warning'
        ],
        'telephoneIsRequired' => [
            'errorCode' => 'telephoneIsRequired',
            'errorMessage' => 'Telephone number is required.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorType' => 'warning'
        ],
        'appointmentNotFound' => [
            'errorCode' => 'appointmentNotFound',
            'errorMessage' => 'Maybe you have already canceled your appointment? Otherwise, please check that you have used the correct link.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'authKeyMismatch' => [
            'errorCode' => 'authKeyMismatch',
            'errorMessage' => 'Invalid authentication key.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorType' => 'warning'
        ],
        'noAppointmentForThisScope' => [
            'errorCode' => 'noAppointmentForThisScope',
            'errorMessage' => 'Please try again at a later time.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'info'
        ],
        'tooManyAppointmentsWithSameMail' => [
            'errorCode' => 'tooManyAppointmentsWithSameMail',
            'errorMessage' => 'You can only book a limited number of appointments with your e-mail address. Please cancel another appointment before you book a new one.',
            'statusCode' => self::HTTP_NOT_ACCEPTABLE,
            'errorType' => 'error'
        ],
        'scopesNotFound' => [
            'errorCode' => 'scopesNotFound',
            'errorMessage' => 'No scopes found.',
            'statusCode' => self::HTTP_NOT_FOUND,
            'errorType' => 'error'
        ],
        'preconfirmationExpired' => [
            'errorCode' => 'preconfirmationExpired',
            'statusCode' => self::HTTP_BAD_REQUEST,
            'errorMessage' => 'Unfortunately, the time for activating your appointment has expired. Please schedule the appointment again.',
            'errorType' => 'error'
        ],

        //Middleware exceptions
        'ipBlacklisted' => [
            'errorCode' => 'IP_BLACKLISTED',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Access denied - IP address is blacklisted.',
            'errorType' => 'error'
        ],
        'rateLimitExceeded' => [
            'errorCode' => 'rateLimitExceeded',
            'statusCode' => self::HTTP_TOO_MANY_REQUESTS,
            'errorMessage' => 'Rate limit exceeded. Please try again later.',
            'errorType' => 'warning'
        ],
        'requestEntityTooLarge' => [
            'errorCode' => 'requestEntityTooLarge',
            'statusCode' => self::HTTP_REQUEST_ENTITY_TOO_LARGE,
            'errorMessage' => 'Request entity too large.',
            'errorType' => 'warning'
        ],
        'securityHeaderViolation' => [
            'errorCode' => 'securityHeaderViolation',
            'statusCode' => self::HTTP_FORBIDDEN,
            'errorMessage' => 'Security policy violation.',
            'errorType' => 'error'
        ]
    ];

    /**
     * Get an error message by key.
     *
     * @param string $key The error message key.
     * @return array The error message array.
     */
    public static function get(string $key): array
    {
        if (isset(self::MESSAGES[$key])) {
            return self::MESSAGES[$key];
        }

        return [
            'errorCode' => 'unknownError',
            'statusCode' => self::HTTP_UNKNOWN,
            'errorMessage' => 'An unknown error occurred.',
            'errorType' => 'error'
        ];
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
