<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Mail\Helper;

class ProcessPlainText
{
    public const int MAX_CUSTOM_TEXTFIELD_CHARS = 250;

    public const int MAX_AMENDMENT_CHARS = 500;

    public static function normalize(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }
        $normalized = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = str_replace(["\r\n", "\r"], "\n", $normalized);
        $normalized = preg_replace('/<\s*br\s*\/?>/iu', "\n", $normalized) ?? $normalized;

        return strip_tags($normalized);
    }
}
