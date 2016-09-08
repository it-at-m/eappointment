<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

class Render extends \BO\Slim\Render
{
    /**
     *
     * @SuppressWarnings(Superglobals)
     * Render Exception Page
     *
     * @return string
     */
    public static function error($request, $exception)
    {
        if ($exception instanceof \Slim\Exception\Stop) {
            return true;
        }
        $servertime = strftime("%F %T");
        $uniqueId = substr(sha1($servertime . rand(1, 60)), 0, 6);
        \App::$log->critical(
            "PHP Fatal Exception #$uniqueId in " . $exception->getFile() . " +" . $exception->getLine() . " : " .
            $exception->getMessage()
        );
        self::lastModified(time(), '0');
        self::html(
            'page/failed.twig',
            array(
                "title" => "Bitte entschuldigen Sie den Fehler",
                "debug" => \App::DEBUG,
                "failed" => $exception->getMessage(),
                "error" => $exception,
                "file" => $exception->getFile(),
                "line" => $exception->getLine(),
                "trace" => $exception->getTraceAsString(),
                "servertime" => $servertime,
                "uniqueid" => $uniqueId,
                "requesturi" => $request->getUri(),
                "requestdata" => htmlspecialchars(json_encode($request, JSON_HEX_QUOT)),
                "requestmethod" => $request->getMethod()
            )
        );
    }
}
