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
    public static function readCurrentHead()
    {
        $headString = "no version control";
        $githead = \App::APP_PATH . '/.git/HEAD';
        if (is_readable($githead)) {
            $headString = trim(fgets(fopen($githead, 'r')));
            $headString = preg_replace('#^.* ([^\s]+)$#', '$1', $headString);
        }
        return $headString;
    }

    public static function readCurrentHash()
    {
        $githash = false;
        $headString = static::readCurrentHead();
        $githashFile = \App::APP_PATH . '/.git/' . $headString;
        if (is_readable($githashFile)) {
            $githash = trim(fgets(fopen($githashFile, 'r')));
        } else {
            $githash = $headString;
        }
        return $githash;
    }

    public static function readCurrentVersion()
    {
        $headString = static::readCurrentHead();
        $headString = preg_replace('#refs/heads/#', '', $headString);
        return $headString;
    }
}
