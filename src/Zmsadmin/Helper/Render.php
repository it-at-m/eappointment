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
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function checkedHtml($errorHandler, $response, $template, $parameters = array(), $status = 200)
    {
        $errorResponse = $errorHandler->getErrorResponse();
        if ($errorResponse instanceof \Psr\Http\Message\ResponseInterface) {
            return $errorResponse;
        } else {
            if (isset($errorResponse['error']) && null !== isset($errorResponse['error'])) {
                $parameters['error'] = $errorResponse['error'];
            }
            if (isset($errorResponse['notice']) && null !== isset($errorResponse['notice'])) {
                $parameters['notice'] = $errorResponse['notice'];
            }
            if (isset($errorResponse['success']) && null !== isset($errorResponse['success'])) {
                $parameters['success'] = $errorResponse['success'];
            }
        }
        return \BO\Slim\Render::withHtml($response, $template, $parameters, $status);
    }

    /**
     * @param String $route_name
     * @param Array $arguments parameters in the route path
     * @param Array $parameter parameters to append with "?"
     * @param Int $statuscode see an HTTP reference
     *
     * @return NULL
     */
    public static function checkedRedirect($errorHandler, $route_name, $arguments, $parameters, $statuscode = 302)
    {
        $errorResponse = $errorHandler->getErrorResponse();
        if ($errorResponse instanceof \Slim\Http\Response) {
            return $errorResponse;
        } else {
            if (isset($errorResponse['error']) && null !== isset($errorResponse['error'])) {
                $parameters['error'] = $errorResponse['error'];
            }
            if (isset($errorResponse['notice']) && null !== isset($errorResponse['notice'])) {
                $parameters['notice'] = $errorResponse['notice'];
            }
        }
        return \BO\Slim\Render::redirect($route_name, $arguments, $parameters, $statuscode);
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     * Render Exception Page
     *
     * @return string
     */
    public static function error($exception)
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
                "basket" => $_SESSION['basket'],
                "title" => "Bitte entschuldigen Sie den Fehler",
                "debug" => \App::DEBUG,
                "failed" => $exception->getMessage(),
                "error" => $exception,
                "file" => $exception->getFile(),
                "line" => $exception->getLine(),
                "trace" => $exception->getTraceAsString(),
                "servertime" => $servertime,
                "uniqueid" => $uniqueId,
                "requesturi" => self::$request->getUri(),
                "requestdata" => htmlspecialchars(json_encode(self::$request, JSON_HEX_QUOT)),
                "requestmethod" => self::$request->getMethod()
            )
        );
    }
}
