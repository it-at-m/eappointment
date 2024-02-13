<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim;

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
     */
    public function getParams()
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();
        if ($postParams) {
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
     */
    public function getCookieParam($key, $default = null)
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
     */
    public function getBasePath(): string
    {
        $basePath = getenv('ZMS_MODULE_BASEPATH') !== false ? getenv('ZMS_MODULE_BASEPATH') : '';
        if (empty($basePath)) {
            $serverParams = $this->getServerParams();

            if (!isset($serverParams['REQUEST_URI']) || !isset($serverParams['SCRIPT_NAME'])) {
                return $basePath;
            }

            while (min(strlen($serverParams['REQUEST_URI']), strlen($serverParams['SCRIPT_NAME'])) > strlen($basePath)
                && strncmp($serverParams['REQUEST_URI'], $serverParams['SCRIPT_NAME'], strlen($basePath) + 1) === 0
            ) {
                $basePath = substr($serverParams['REQUEST_URI'], 0, strlen($basePath) + 1);
            }
        }

        return rtrim($basePath, '/');
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
     */
    public function getBaseUrl()
    {
        $scheme = $this->getUri()->getScheme();
        $authority = $this->getUri()->getAuthority();
        $basePath = $this->getBasePath();

        if ($authority !== '' && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme !== '' ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }
}
