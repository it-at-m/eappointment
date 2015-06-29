<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Helper
{
    public static function proxySanitizeUri($uri)
    {
        $uri = str_replace(':80/', '/', $uri);
        return $uri;
    }
}
