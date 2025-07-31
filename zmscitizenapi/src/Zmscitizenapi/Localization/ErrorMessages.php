<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Localization;

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
            'errorHeader' => 'An unknown error occurred.',
            'errorMessage' => 'An unknown error occurred.'
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
