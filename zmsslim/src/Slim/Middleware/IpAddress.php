<?php

/**
 * @copyright MIT
 * derived from akrabat/rka-ip-address-middleware via composer.phar
 **/

namespace BO\Slim\Middleware;

use BO\Slim\Factory\ResponseFactory;
use BO\Slim\Helper\ClientIp;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IpAddress
{
    /**
     * Enable checking of proxy headers (X-Forwarded-For to determined client IP.
     *
     * Defaults to false as only $_SERVER['REMOTE_ADDR'] is a trustworthy source
     * of IP address.
     *
     * @var bool
     */
    protected bool $checkProxyHeaders;

    /**
     * List of trusted proxy IP addresses
     *
     * If not empty, then one of these IP addresses must be in $_SERVER['REMOTE_ADDR']
     * in order for the proxy headers to be looked at.
     * If TRUE, trust only a platform proxy: loopback, a private address, or TRUSTED_PROXIES.
     *
     * @var array|true
     */
    protected mixed $trustedProxies;

    /**
     * Name of the attribute added to the ServerRequest object
     *
     * @var string
     */
    protected string $attributeName = 'ip_address';

    /**
     * List of proxy headers inspected for the client IP address
     *
     * @var array
     */
    protected array $headersToInspect = [
        'X-Forwarded-For',
    ];

    /**
     * Constructor
     *
     * @param bool $checkProxyHeaders Whether to use proxy headers to determine client IP
     * @param mixed $trustedProxies   List of proxy addresses, or TRUE for loopback, private, and TRUSTED_PROXIES
     * @param string|null $attributeName   Name of attribute added to ServerRequest object
     * @param array $headersToInspect List of headers to inspect
     */
    public function __construct(
        bool $checkProxyHeaders = false,
        mixed $trustedProxies = [],
        ?string $attributeName = null,
        array $headersToInspect = []
    ) {
        $this->checkProxyHeaders = $checkProxyHeaders;
        $this->trustedProxies = $trustedProxies;

        if ($attributeName !== null && $attributeName !== '') {
            $this->attributeName = $attributeName;
        }
        if ($headersToInspect !== []) {
            $this->headersToInspect = $headersToInspect;
        }
    }

    /**
     * Set the "$attributeName" attribute to the client's IP address as determined from
     * the proxy header (X-Forwarded-For or from $_SERVER['REMOTE_ADDR']
     *
     * @param ServerRequestInterface $request PSR7 request
     * @param RequestHandlerInterface|null $next Next middleware
     *
     * @return ResponseInterface
     * @psalm-api Called by Slim as middleware.
     */
    public function __invoke(ServerRequestInterface $request, ?RequestHandlerInterface $next): ResponseInterface
    {
        if (!$next) {
            return (new ResponseFactory())->createResponse();
        }

        $ipAddress = $this->determineClientIpAddress($request);
        $request = $request->withAttribute($this->attributeName, $ipAddress);

        return $next->handle($request);
    }

    /**
     * Find out the client's IP address from the headers available to us
     *
     * @param  ServerRequestInterface $request PSR-7 Request
     * @return string|null
     */
    protected function determineClientIpAddress(ServerRequestInterface $request): ?string
    {
        $ipAddress = null;

        $serverParams = $request->getServerParams();
        if (isset($serverParams['REMOTE_ADDR']) && $this->isValidIpAddress((string) $serverParams['REMOTE_ADDR'])) {
            $ipAddress = $serverParams['REMOTE_ADDR'];
        }

        if ($ipAddress !== null && $this->isCheckProxyHeaders($ipAddress) && $this->inspectsForwardedFor()) {
            $forwarded = $request->hasHeader('X-Forwarded-For')
                ? $request->getHeaderLine('X-Forwarded-For')
                : null;
            $ipAddress = ClientIp::appendedByTrustedProxy($ipAddress, $forwarded);
        }

        return $ipAddress;
    }

    protected function isCheckProxyHeaders(?string $ipAddress): bool
    {
        if (!$this->checkProxyHeaders || $ipAddress === null) {
            return false;
        }

        if ($this->trustedProxies === true || $this->trustedProxies === []) {
            return ClientIp::isTrustedProxy($ipAddress);
        }

        return in_array($ipAddress, $this->trustedProxies, true);
    }

    private function inspectsForwardedFor(): bool
    {
        return in_array('X-Forwarded-For', $this->headersToInspect, true);
    }

    /**
     * Check that a given string is a valid IP address
     *
     * @param  string  $ipString
     * @return boolean
     */
    protected function isValidIpAddress(string $ipString): bool
    {
        $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        if (filter_var($ipString, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }
        return true;
    }
}
