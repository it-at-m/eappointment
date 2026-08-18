<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Request extends \Slim\Psr7\Request
{
    /**
     * Fetch request parameter value from body or query string (in that order).
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param  string $key     The parameter key.
     * @param  mixed  $default The default value.
     *
     * @return mixed
     * @psalm-api
     */
    public function getParam(string $key, $default = null)
    {
        $postParams = $this->getParsedBody();
        $getParams = $this->getQueryParams();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        }

        return $result;
    }

    /**
     * @deprecated
     * please use explicitly the query params or parsed request body params
     *
     * @return mixed[]
     * @psalm-api
     */
    public function getParams(): array
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if (is_array($postParams) || is_object($postParams)) {
            $params = array_replace($params, (array)$postParams);
        }

        return $params;
    }

    /**
     * Fetch cookie value from cookies sent by the client to the server.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @param string $key     The attribute name.
     * @param mixed  $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     * @psalm-api
     */
    public function getCookieParam(string $key, $default = null)
    {
        $cookies = $this->getCookieParams();
        $result = $default;
        if (isset($cookies[$key])) {
            $result = $cookies[$key];
        }

        return $result;
    }

    /**
     * @deprecated (use SlimApp::getBasePath() or resolve a named route instead)
     * @return string
     * @psalm-api
     */
    public function getBasePath(): string
    {
        return $this->resolveBasePath();
    }

    /**
     * Return the fully qualified base URL.
     *
     * Note that this method never includes a trailing /
     *
     * This method is not part of PSR-7.
     *
     * @deprecated
     * @return string
     * @psalm-api
     */
    public function getBaseUrl(): string
    {
        $scheme = $this->getUri()->getScheme();
        $authority = $this->getUri()->getAuthority();
        $basePath = $this->resolveBasePath();

        if ($authority !== '' && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme !== '' ? $scheme . ':' : '')
            . ($authority !== '' ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }

    private function resolveBasePath(): string
    {
        $envBasePath = getenv('ZMS_MODULE_BASEPATH');
        $basePath = $envBasePath !== false ? $envBasePath : '';
        if ($basePath === '') {
            $serverParams = $this->getServerParams();
            $requestUri = $serverParams['REQUEST_URI'] ?? null;
            $scriptName = $serverParams['SCRIPT_NAME'] ?? null;
            if (!is_string($requestUri) || !is_string($scriptName)) {
                return $basePath;
            }

            while (
                min(strlen($requestUri), strlen($scriptName)) > strlen($basePath)
                && strncmp($requestUri, $scriptName, strlen($basePath) + 1) === 0
            ) {
                $nextPath = substr($requestUri, 0, strlen($basePath) + 1);
                if ($nextPath === false) {
                    break;
                }
                $basePath = $nextPath;
            }
        }

        return rtrim($basePath, '/');
    }
}
