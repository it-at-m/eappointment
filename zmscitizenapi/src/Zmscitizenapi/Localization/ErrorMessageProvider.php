<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Localization;

class ErrorMessageProvider
{
    public static function getMessage(string $messageKey, string $lang = 'EN'): string
    {
        // Default to English if unknown language code is passed
        $messages = ($lang === 'DE') ? ErrorMessages::DE : ErrorMessages::EN;

        // Return the message if it exists; otherwise provide a fallback
        return $messages[$messageKey] ?? 'Undefined error message';
    }
}