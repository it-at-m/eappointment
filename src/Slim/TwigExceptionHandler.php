<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
  *
  */
class TwigExceptionHandler
{

    const DEFAULT_TEMPLATE = "exception/default.twig";

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception
    ) {
        return static::withHtml($request, $response, $exception, 500);
    }

    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        $status = 500
    ) {
        try {
            if ($exception instanceof \Slim\Exception\Stop) {
                return true;
            }
            $request = Controller::prepareRequest($request);
            if ($exception->getCode() >= 200) {
                $status = $exception->getCode();
            }
            $template = self::getExceptionTemplate($exception);
            $extendedInfo = self::getExtendedExceptionInfo($exception, $request);
            if ($status >= 500 || $status < 200 || !$status || $template == static::DEFAULT_TEMPLATE) {
                $logInfo = $extendedInfo;
                unset($logInfo['responsedata']);
                unset($logInfo['exception']);
                //ksort($logInfo);
                $logText = json_encode($logInfo);
                $logText = preg_replace('#\s+#', ' ', $logText);
                $logText = preg_replace('#"#', "", $logText);
                $logText = preg_replace('#'.preg_quote('\\/').'#', "/", $logText);
                $logText = preg_replace('#'.preg_quote('\\').'#', "⑊", $logText);
                $logText = preg_replace('#⑊⑊#', "⑊", $logText);
                $logText = preg_replace('#⑊n#', " ", $logText);
                $logText = preg_replace('#(/[^/\s]+)+/([^/\s]+/[^/\s]+)\.php#', "$2.php", $logText);
                $logText = preg_replace('#'.preg_quote(\APP::APP_PATH).'/?#', "", $logText);
                \App::$log->critical("PHP-Exception #{$extendedInfo['uniqueid']}: ". $logText);
                /*
                \App::$log->critical(
                    "PHP Fatal Exception #{$extendedInfo['uniqueid']}"
                    . " in {$extendedInfo['file']} +{$extendedInfo['line']} : " .
                    $exception->getMessage()
                    . " || Trace: " . str_replace("\n", " ||  ", substr($exception->getTraceAsString(), 0, 1024))
                );
                */
            }
            $response = Render::withLastModified($response, time(), '0');
            return Render::withHtml(
                $response,
                $template,
                array_merge($extendedInfo, array(
                    "title" => "Bitte entschuldigen Sie den Fehler",
                )),
                $status
            );
        } catch (\Throwable $subexception) {
            error_log(
                "Not catchable Exception: "
                . $exception->getMessage()
                . " "
                . $exception->getFile()
                . ":"
                . $exception->getLine()
                . " "
                . $exception->getTraceAsString
                ." ---- because of "
                . $subexception->getMessage()
                . " "
                . $subexception->getTraceAsString()
            );
        }
    }

    public static function getExceptionTemplate(\Throwable $exception)
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
            $template = static::DEFAULT_TEMPLATE;
        }
        return $template;
    }

    protected static function getRequestData(RequestInterface $request)
    {
        $requestdata = array_merge((array)$request->getQueryParams(), (array)$request->getParsedBody());
        $json_opt = JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_HEX_AMP;
        if (json_decode((string)$request->getBody())) {
            $requestdata = json_encode(json_decode((string)$request->getBody()), $json_opt);
        } else {
            $requestdata = json_encode($requestdata, $json_opt);
        }
        return $requestdata;
    }

    public static function getExtendedExceptionInfo(\Throwable $exception, RequestInterface $request)
    {
        $servertime = strftime("%F %T");
        $exceptionclass = get_class($exception);
        if (isset($exception->template)) {
            $exceptionclass = $exception->template;
        }
        $response = null;
        $responsedata = '';
        $apirequest = null;
        $apirequestdata = '';
        $apirequesturi = '';
        $apirequestmethod = '';
        if (isset($exception->request)) {
            $apirequest = $exception->request;
            $apirequest = $apirequest->withUri($apirequest->getUri()->withUserInfo(''));
            $apirequestdata = static::getRequestData($apirequest);
            $apirequesturi = $apirequest->getUri();
            $apirequestmethod = $apirequest->getMethod();
        }
        $request = $request->withUri($request->getUri()->withUserInfo(''));
        if (isset($exception->response)) {
            $response = $exception->response;
            $responsedata = (string)$response->getBody();
        }
        $trace = substr($exception->getTraceAsString(), 0, 2048);

        if (isset($exception->trace)) {
            $trace = $exception->trace;
        }
        $requestdata = static::getRequestData($request);
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
            "exceptionclass" => $exceptionclass,
            "requesturi" => $request->getUri(),
            "apirequesturi" => $apirequesturi,
            "_file" => $exception->getFile(),
            "_line" => $exception->getLine(),
            "_trace" => $trace,
            "exception" => $exception,
            "exceptioncode" => $exception->getCode(),
            "debug" => \App::DEBUG,
            "data" => $data,
            "failed" => $exception->getMessage(),
            "basefile" => basename($exception->getFile(), '.php'),
            "servertime" => $servertime,
            "uniqueid" => $uniqueId,
            "request" => $request,
            "requestmethod" => $request->getMethod(),
            "apirequest" => $apirequest,
            "apirequestmethod" => $apirequestmethod,
            "response" => $response,
            "x-requestdata" => $requestdata,
            "x-requestdata_api" => $apirequestdata,
            "x-responsedata" => $responsedata,
        ), $templatedata);
    }
}
