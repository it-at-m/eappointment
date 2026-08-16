<?php

namespace BO\Slim\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use BO\Slim\Factory\ResponseFactory;

/**
 *
 * Sends the session headers in the Response, putting them under manual control
 * rather than relying on PHP to send them itself.
 *
 * This works correctly only if you have these settings:
 *
 * ```
 * ini_set('session.use_trans_sid', false);
 * ini_set('session.use_cookies', false);
 * ini_set('session.use_only_cookies', true);
 * ini_set('session.cache_limiter', '');
 * ```
 *
 * Note that the Last-Modified value will not be the last time the session was
 * saved, but instead the current `time()`.
 *
 *
 * @psalm-api
 */
class SessionHeadersHandler
{
    /**
     * The timestamp for "already expired."
     */
    const string EXPIRED = 'Thu, 19 Nov 1981 08:52:00 GMT';

    /**
     *
     * The cache limiter type, if any.
     *
     * @see session_cache_limiter()
     *
     */
    protected string $cacheLimiter;

    /**
     *
     * The cache expiration time in minutes.
     *
     * @see session_cache_expire()
     *
     */
    protected int $cacheExpire;

    /**
     *
     * The current Unix timestamp.
     *
     */
    protected int $time;

    /**
     *
     * Constructor.
     *
     * @param string $cacheLimiter The cache limiter type.
     *
     * @param int $cacheExpire The cache expiration time in minutes.
     *
     * @throws RuntimeException when the ini settings are incorrect.
     *
     */
    public function __construct(string $cacheLimiter = 'nocache', int $cacheExpire = 180)
    {
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_cookies', false);
        ini_set('session.use_only_cookies', true);
        ini_set('session.cache_limiter', '');

        if (ini_get('session.use_trans_sid') != false) {
            $message = "The .ini setting 'session.use_trans_sid' must be false.";
            throw new RuntimeException($message);
        }

        if (ini_get('session.use_cookies') != false) {
            $message = "The .ini setting 'session.use_cookies' must be false.";
            throw new RuntimeException($message);
        }

        if (ini_get('session.use_only_cookies') != true) {
            $message = "The .ini setting 'session.use_only_cookies' must be true.";
            throw new RuntimeException($message);
        }

        if (ini_get('session.cache_limiter') !== '') {
            $message = "The .ini setting 'session.cache_limiter' must be an empty string.";
            throw new RuntimeException($message);
        }

        $this->cacheLimiter = $cacheLimiter;
        $this->cacheExpire = $cacheExpire;
        $this->time = time();
    }

    /**
     *
     * Sends the session headers in the Response.
     *
     * @param Request $request The HTTP request.
     * @param RequestHandlerInterface|null $next The next middleware in the queue.
     *
     * @return Response
     *
     */
    public function __invoke(Request $request, ?RequestHandlerInterface $next): Response
    {
        // retain the incoming session id
        $oldId = '';
        $oldName = session_name();
        $cookies = $request->getCookieParams();
        if (is_string($oldName) && $oldName !== '') {
            $cookieId = $cookies[$oldName] ?? null;
            if (is_string($cookieId) && $cookieId !== '') {
                $oldId = $cookieId;
                session_id($oldId);
            }
        }

        // invoke the next middleware
        if (null !== $next) {
            $response = $next->handle($request);
        } else {
            $response = (new ResponseFactory())->createResponse();
        }

        // record the current time
        $this->time = time();

        // is the session id still the same?
        $newId = session_id();
        if (is_string($newId) && $newId !== $oldId) {
            // one of the middlewares changed it; send the new one.
            // capture any session name changes as well.
            $response = $this->withNewSessionCookie($response, $newId);
        }

        // if there is a session id, also send the cache limiters
        if (is_string($newId) && $newId !== '') {
            $response = $this->withCacheLimiter($response);
        }

        // done!
        return $response;
    }

    /**
     *
     * Adds a session cookie header to the Response.
     *
     * @param Response $response The HTTP response.
     *
     * @param string $sessionId The new session ID.
     *
     * @return Response
     *
     * @see https://github.com/php/php-src/blob/PHP-5.6.20/ext/session/session.c#L1337-L1408
     *
     */
    protected function withNewSessionCookie(Response $response, string $sessionId): Response
    {
        $sessionName = session_name();
        if (!is_string($sessionName)) {
            return $response;
        }
        $cookie = urlencode($sessionName) . '=' . urlencode($sessionId);

        $params = session_get_cookie_params();

        $lifetime = $params['lifetime'] ?? 0;
        if ($lifetime !== 0) {
            $expires = $this->timestamp($lifetime);
            $cookie .= "; expires={$expires}; max-age={$lifetime}";
        }

        $domain = $params['domain'] ?? '';
        if ($domain !== '') {
            $cookie .= "; domain={$domain}";
        }

        $path = $params['path'] ?? '';
        if ($path !== '') {
            $cookie .= "; path={$path}";
        }

        if (($params['secure'] ?? false) === true) {
            $cookie .= '; secure';
        }

        if (($params['httponly'] ?? false) === true) {
            $cookie .= '; httponly';
        }

        return $response->withAddedHeader('Set-Cookie', $cookie);
    }

    /**
     *
     * Returns a cookie-formatted timestamp.
     *
     * @param int $adj Adjust the time by this many seconds before formatting.
     *
     * @return string
     *
     */
    protected function timestamp(int $adj = 0): string
    {
        return gmdate('D, d M Y H:i:s T', $this->time + $adj);
    }

    /**
     *
     * Returns a Response with added cache limiter headers.
     *
     * @param Response $response The HTTP response.
     *
     * @return Response
     *
     */
    protected function withCacheLimiter(Response $response): Response
    {
        switch ($this->cacheLimiter) {
            case 'public':
                return $this->cacheLimiterPublic($response);
            case 'private_no_expire':
                return $this->cacheLimiterPrivateNoExpire($response);
            case 'private':
                return $this->cacheLimiterPrivate($response);
            case 'nocache':
                return $this->cacheLimiterNocache($response);
            default:
                return $response;
        }
    }

    /**
     *
     * Returns a Response with 'public' cache limiter headers.
     *
     * @param Response $response The HTTP response.
     *
     * @return Response
     *
     * @see https://github.com/php/php-src/blob/PHP-5.6.20/ext/session/session.c#L1196-L1213
     *
     */
    protected function cacheLimiterPublic(Response $response): Response
    {
        $maxAge = $this->cacheExpire * 60;
        $expires = $this->timestamp($maxAge);
        $cacheControl = "public, max-age={$maxAge}";
        $lastModified = $this->timestamp();

        return $response
            ->withAddedHeader('Expires', $expires)
            ->withAddedHeader('Cache-Control', $cacheControl)
            ->withAddedHeader('Last-Modified', $lastModified);
    }

    /**
     *
     * Returns a Response with 'private_no_expire' cache limiter headers.
     *
     * @param Response $response The HTTP response.
     *
     * @return Response
     *
     * @see https://github.com/php/php-src/blob/PHP-5.6.20/ext/session/session.c#L1215-L1224
     *
     */
    protected function cacheLimiterPrivateNoExpire(Response $response): Response
    {
        $maxAge = $this->cacheExpire * 60;
        $cacheControl = "private, max-age={$maxAge}, pre-check={$maxAge}";
        $lastModified = $this->timestamp();

        return $response
            ->withAddedHeader('Cache-Control', $cacheControl)
            ->withAddedHeader('Last-Modified', $lastModified);
    }

    /**
     *
     * Returns a Response with 'private' cache limiter headers.
     *
     * @param Response $response The HTTP response.
     *
     * @return Response
     *
     * @see https://github.com/php/php-src/blob/PHP-5.6.20/ext/session/session.c#L1226-L1231
     *
     */
    protected function cacheLimiterPrivate(Response $response): Response
    {
        if (0 == count($response->getHeader('Expires'))) {
            $response = $response->withAddedHeader('Expires', self::EXPIRED);
        }
        return $this->cacheLimiterPrivateNoExpire($response);
    }

    /**
     *
     * Returns a Response with 'nocache' cache limiter headers.
     *
     * @param Response $response The HTTP response.
     *
     * @return Response
     *
     * @see https://github.com/php/php-src/blob/PHP-5.6.20/ext/session/session.c#L1233-L1243
     *
     */
    protected function cacheLimiterNocache(Response $response): Response
    {
        if (0 == count($response->getHeader('Expires'))) {
            $response = $response->withAddedHeader('Expires', self::EXPIRED);
        }
        return $response
            ->withAddedHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
            )
            ->withAddedHeader('Pragma', 'no-cache');
    }
}
