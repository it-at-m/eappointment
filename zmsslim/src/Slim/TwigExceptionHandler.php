<?php

/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;

class TwigExceptionHandler implements ErrorHandlerInterface
{
    const string DEFAULT_TEMPLATE = "exception/default.twig";

    /**
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     * @param ServerRequestInterface $request
     * @param \Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     */
    #[\Override]
    public function __invoke(
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {

        $decoratedRequest = \BO\Slim\Middleware\ZmsSlimRequest::getDecoratedRequest($request);
        $response = \App::$slim->getResponseFactory()->createResponse();
        return static::withHtml($decoratedRequest, $response, $exception);
    }

    public static function withHtml(
        RequestInterface $request,
        ResponseInterface $response,
        \Throwable $exception,
        int $status = 500
    ): ResponseInterface {
        try {
            $request = Controller::prepareRequest($request);
            $code = (int) $exception->getCode();
            if ($code >= 200) {
                $status = $code;
            }
            $template = self::getExceptionTemplate($exception);
            $extendedInfo = self::getExtendedExceptionInfo($exception, $request);
            if ($status >= 500 || $status < 200 || $template == static::DEFAULT_TEMPLATE) {
                $logInfo = $extendedInfo;
                unset($logInfo['responsedata']);
                unset($logInfo['exception']);
                unset($logInfo['workstation']);
                //ksort($logInfo);
                // Some error-reporting is limited to a defined amount of chars
                // Remove unnecessary chars, for ordering see self::getExtendedExceptionInfo()
                $logText = json_encode($logInfo);
                $logText = is_string($logText) ? $logText : '';
                $logText = self::replaceLogText('#' . preg_quote('\\/') . '#', "/", $logText);
                $logText = self::replaceLogText('#' . preg_quote('\\"') . '#', "", $logText);
                $logText = self::replaceLogText('#' . preg_quote('\\n') . '#', " ", $logText);
                $logText = self::replaceLogText('#' . preg_quote('\\') . '#', ".", $logText);
                $logText = self::replaceLogText('#"#', "", $logText);
                $logText = self::replaceLogText('#\s+#', ' ', $logText);
                $logText = self::replaceLogText('#\.\.#', ".", $logText);
                $logText = self::replaceLogText('#(/[^/\s]+)+/([^/\s]+/[^/\s]+)\.php#', "$2.php", $logText);
                $logText = self::replaceLogText('#' . preg_quote(\App::APP_PATH) . '/?#', "", $logText);
                \App::$log->critical("PHP-Exception #{$extendedInfo['uniqueid']}: " . $logText);
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
            \App::$log->critical('Not catchable exception while rendering error page', [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'cause' => get_class($subexception),
                'causeMessage' => $subexception->getMessage(),
                'causeTrace' => $subexception->getTraceAsString(),
            ]);

            $fallback = $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'text/plain; charset=UTF-8');
            $fallback->getBody()->write('Internal Server Error');

            return $fallback;
        }
    }

    public static function getExceptionTemplate(\Throwable $exception): string
    {
        $container = \App::$slim->getContainer();
        if ($container === null) {
            return static::DEFAULT_TEMPLATE;
        }
        $twig = $container->get('view');
        if (!is_object($twig) || !method_exists($twig, 'getLoader')) {
            return static::DEFAULT_TEMPLATE;
        }
        $loader = $twig->getLoader();
        $templateField = self::getExceptionField($exception, 'template');
        if (is_string($templateField) && $templateField !== '') {
            $classname = $templateField;
        } else {
            $classname = get_class($exception);
        }
        $classname = strtolower($classname);
        $classname = preg_replace('#[\\\]+#', '/', $classname) ?? $classname;
        $template = "exception/$classname.twig";

        if (!is_object($loader) || !method_exists($loader, 'exists') || !$loader->exists($template)) {
            $template = static::DEFAULT_TEMPLATE;
        }
        return $template;
    }

    /**
     * @return false|string
     */
    protected static function getRequestData(RequestInterface $request): string|false
    {
        $queryParams = [];
        $parsedBody = [];
        if ($request instanceof ServerRequestInterface) {
            $queryParams = (array) $request->getQueryParams();
            $parsedBody = (array) $request->getParsedBody();
        } else {
            parse_str($request->getUri()->getQuery(), $queryParams);
        }
        $requestdata = array_merge($queryParams, $parsedBody);
        $json_opt = JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_HEX_AMP;
        if (json_decode((string)$request->getBody())) {
            $requestdata = json_encode(json_decode((string)$request->getBody()), $json_opt);
        } else {
            $requestdata = json_encode($requestdata, $json_opt);
        }
        return $requestdata;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function getExtendedExceptionInfo(\Throwable $exception, RequestInterface $request): array
    {
        $servertime = Helper::getFormatedDates((new \DateTimeImmutable())->getTimestamp(), 'yyyy-MM-dd HH:mm:ss');
        $servertime = $servertime !== false ? $servertime : '';
        $templateField = self::getExceptionField($exception, 'template');
        $exceptionclass = is_string($templateField) && $templateField !== ''
            ? $templateField
            : get_class($exception);
        $response = null;
        $responsedata = '';
        $apirequest = null;
        $apirequestdata = '';
        $apirequesturi = '';
        $apirequestmethod = '';
        $apiRequestField = self::getExceptionField($exception, 'request');
        if ($apiRequestField instanceof RequestInterface) {
            $apirequest = $apiRequestField;
            $apirequest = $apirequest->withUri($apirequest->getUri()->withUserInfo(''));
            $apirequestdata = static::getRequestData($apirequest);
            $apirequesturi = $apirequest->getUri();
            $apirequestmethod = $apirequest->getMethod();
        }
        $routename = '';
        if ($request instanceof ServerRequestInterface) {
            $route = $request->getAttribute('route');
            if ($route instanceof \Slim\Routing\Route) {
                $routename = $route->getName() ?? '';
            }
        }

        // Do not log username or password
        $request = $request->withUri($request->getUri()->withUserInfo(''));
        $apiResponseField = self::getExceptionField($exception, 'response');
        if ($apiResponseField instanceof ResponseInterface) {
            $response = $apiResponseField;
            $responsedata = (string)$response->getBody();
        }
        $trace = substr($exception->getTraceAsString(), 0, 2048);

        $traceField = self::getExceptionField($exception, 'trace');
        if (is_string($traceField) && $traceField !== '') {
            $trace = $traceField;
        }
        $requestdata = static::getRequestData($request);
        $uniqueId = substr(sha1($servertime . rand(1, 60)), 0, 6);
        $dataField = self::getExceptionField($exception, 'data');
        $data = is_array($dataField) ? $dataField : [];
        $templateDataField = self::getExceptionField($exception, 'templatedata');
        $templatedata = is_array($templateDataField) ? $templateDataField : [];

        // Due to shortened error logs in some reportings, important informations first!
        return array_merge(array(
            "exceptionclass" => $exceptionclass,
            "requesturi" => $request->getUri()->getPath(),
            "apirequesturi" => (string)$apirequesturi,
            "route" => $routename,
            "_file" => $exception->getFile(),
            "_line" => $exception->getLine(),
            "failed" => $exception->getMessage(),
            "requestmethod" => $request->getMethod(),
            "data" => $data,
            "x-requestdata" => $requestdata,
            "servertime" => $servertime,
            "exception" => $exception,
            "exceptioncode" => $exception->getCode(),
            "basefile" => basename($exception->getFile(), '.php'),
            "x-requestdata_api" => $apirequestdata,
            "_trace" => $trace,
            "debug" => \App::DEBUG,
            "uniqueid" => $uniqueId,
            "request" => $request,
            "apirequest" => $apirequest,
            "apirequestmethod" => $apirequestmethod,
            "response" => $response,
            "x-responsedata" => $responsedata,
        ), $templatedata);
    }

    private static function replaceLogText(string $pattern, string $replacement, string $subject): string
    {
        if ($pattern === '') {
            return $subject;
        }
        return preg_replace($pattern, $replacement, $subject) ?? $subject;
    }

    private static function getExceptionField(\Throwable $exception, string $field): mixed
    {
        $vars = get_object_vars($exception);
        return array_key_exists($field, $vars) ? $vars[$field] : null;
    }
}
