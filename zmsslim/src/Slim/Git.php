<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

/**
  * Helper for GIT integration
  *
  */
class Git
{
    public static function readCurrentHead(): string|null
    {
        $headString = "no version control";
        $githead = \App::APP_PATH . '/.git/HEAD';
        if (is_readable($githead)) {
            $handle = fopen($githead, 'r');
            if ($handle !== false) {
                $line = fgets($handle);
                fclose($handle);
                if ($line !== false) {
                    $headString = trim($line);
                    $headString = preg_replace('#^.* ([^\s]+)$#', '$1', $headString) ?? $headString;
                }
            }
        }
        return $headString;
    }

    public static function readCurrentHash(): string|null
    {
        $headString = static::readCurrentHead();
        $githashFile = \App::APP_PATH . '/.git/' . ($headString ?? '');
        if ($headString !== null && is_readable($githashFile)) {
            $handle = fopen($githashFile, 'r');
            if ($handle !== false) {
                $line = fgets($handle);
                fclose($handle);
                if ($line !== false) {
                    return trim($line);
                }
            }
        }

        return $headString;
    }

    /**
     * @return null|string|string[]
     *
     */
    public static function readCurrentVersion(): array|string|null
    {
        $headString = static::readCurrentHead();
        if ($headString === null) {
            return null;
        }
        $headString = preg_replace('#refs/heads/#', '', $headString) ?? $headString;
        return $headString;
    }
}
