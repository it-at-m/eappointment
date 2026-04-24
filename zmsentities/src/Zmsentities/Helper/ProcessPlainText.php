<?php

namespace BO\Zmsentities\Helper;

/**
 * Plain-text normalization for process amendment and custom text fields.
 */
class ProcessPlainText
{
    public const MAX_CUSTOM_TEXTFIELD_CHARS = 255;

    public const MAX_AMENDMENT_CHARS = 500;

    /**
     * Strip HTML, decode entities, normalize line breaks to "\n" for storage/display.
     */
    public static function normalize(?string $input): string
    {
        if ($input === null || $input === '') {
            return '';
        }
        $s = (string) $input;
        $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = preg_replace('/<\s*br\s*\/?>/iu', "\n", $s) ?? $s;
        $s = strip_tags($s);

        return $s;
    }

    public static function charLength(?string $input): int
    {
        return mb_strlen(self::normalize($input), 'UTF-8');
    }
}
