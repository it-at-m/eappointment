<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
  * Extension for Twig and Slim
  *
  *  @SuppressWarnings(PublicMethod)
  *  @SuppressWarnings(TooManyMethods)
  */
class TwigExceptionHandler
{

    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $exception,
        $status = 500
    ) {
        if ($exception instanceof \Slim\Exception\Stop) {
            return true;
        }
        if ($exception->getCode() >= 200) {
            $status = $exception->getCode();
        }
        $extendedInfo = self::getExtendedExceptionInfo($exception, $request);
        if ($status >= 500) {
            \App::$log->critical(
                "PHP Fatal Exception #{$extendedInfo['uniqueid']}"
                . " in {$extendedInfo['file']} +{$extendedInfo['line']} : " .
                $exception->getMessage()
                . " || Trace: " . str_replace("\n", " ||  ", substr($exception->getTraceAsString(), 0, 1024))
            );
        }
        $response = Render::withLastModified($response, time(), '0');
        return Render::withHtml(
            $response,
            self::getExceptionTemplate($exception),
            array_merge($extendedInfo, array(
                "title" => "Bitte entschuldigen Sie den Fehler",
            )),
            $status
        );
    }

    public static function getExceptionTemplate(\Exception $exception)
    {
        $twig = \App::$slim->getContainer()->view;
        $loader = $twig->getLoader();
        if (isset($exception->template)) {
            $classname = $exception->template;
        } else {
            $classname = get_class($exception);
        }
        $classname = strtolower($classname);
        $classname = preg_replace('#[\\\]+#', '/', $classname);
        $template = "exception/$classname.twig";
        if (!$loader->exists($template)) {
            $template = "exception/default.twig";
        }
        return $template;
    }

    public static function getExtendedExceptionInfo(\Exception $exception, RequestInterface $request)
    {
        $servertime = strftime("%F %T");
        $exceptionclass = get_class($exception);
        if (isset($exception->template)) {
            $exceptionclass = $exception->template;
        }
        $response = null;
        $responsedata = '';
        if (\App::DEBUG && isset($exception->request)) {
            $request = $exception->request;
        }
        if (isset($exception->response)) {
            $response = $exception->response;
            $responsedata = (string)$response->getBody();
        }
        $requestdata = array_merge((array)$request->getQueryParams(), (array)$request->getParsedBody());
        $json_opt = JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_HEX_AMP;
        if (json_decode((string)$request->getBody())) {
            $requestdata = json_encode(json_decode((string)$request->getBody()), $json_opt);
        } else {
            $requestdata = json_encode($requestdata, $json_opt);
        }
        $uniqueId = substr(sha1($servertime . rand(1, 60)), 0, 6);
        $data = [];
        if (isset($exception->data)) {
            $data = $exception->data;
        }
        $templatedata = [];
        if (isset($exception->templatedata)) {
            $templatedata = $exception->templatedata;
        }
        return array_merge(array(
            "debug" => \App::DEBUG,
            "data" => $data,
            "failed" => $exception->getMessage(),
            "exception" => $exception,
            "exceptionclass" => $exceptionclass,
            "file" => $exception->getFile(),
            "line" => $exception->getLine(),
            "trace" => $exception->getTraceAsString(),
            "servertime" => $servertime,
            "uniqueid" => $uniqueId,
            "request" => $request,
            "requesturi" => $request->getUri(),
            "requestdata" => $requestdata,
            "requestmethod" => $request->getMethod(),
            "response" => $response,
            "responsedata" => $responsedata,
        ), $templatedata);
    }
}
