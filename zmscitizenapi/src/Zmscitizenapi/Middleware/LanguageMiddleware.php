<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Zmscitizenapi\Services\Core\LoggerService;

class LanguageMiddleware implements MiddlewareInterface
{
    private const SUPPORTED_LANGUAGES = ['de', 'en', 'ua']; // Add more languages here
    private const DEFAULT_LANGUAGE = 'de';
    private const FALLBACK_LANGUAGE = 'en';

    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $parts = explode('/', trim($uri, '/'));
        
        // URL structure is always /terminvereinbarung/api/citizen/{lang}/...
        // So language code is always at index 3 if it exists
        $language = strtolower($parts[3] ?? '');
    
        if (in_array($language, self::SUPPORTED_LANGUAGES)) {
            // Only store language attribute, don't modify the path
            $request = $request->withAttribute('language', $language);
        } else {
            // Set default language
            $request = $request->withAttribute('language', self::DEFAULT_LANGUAGE);
        }
    
        return $handler->handle($request);
    }

    /**
     * Get supported languages
     * @return array
     */
    public static function getSupportedLanguages(): array
    {
        return self::SUPPORTED_LANGUAGES;
    }

    /**
     * Get default language
     * @return string
     */
    public static function getDefaultLanguage(): string
    {
        return self::DEFAULT_LANGUAGE;
    }

    /**
     * Get fallback language
     * @return string
     */
    public static function getFallbackLanguage(): string
    {
        return self::FALLBACK_LANGUAGE;
    }

    /**
     * Normalize language code
     * @param string|null $language
     * @return string
     */
    public static function normalizeLanguage(?string $language): string
    {
        $language = strtolower($language ?? self::DEFAULT_LANGUAGE);
        return in_array($language, self::SUPPORTED_LANGUAGES) ? $language : self::DEFAULT_LANGUAGE;
    }
}