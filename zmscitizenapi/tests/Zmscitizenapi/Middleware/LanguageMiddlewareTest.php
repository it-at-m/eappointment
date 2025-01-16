<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Middleware;

use BO\Zmscitizenapi\Middleware\LanguageMiddleware;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LanguageMiddlewareTest extends TestCase
{
    private LanguageMiddleware $middleware;
    private LoggerService $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerService::class);
        $this->middleware = new LanguageMiddleware($this->logger);
    }

    public function testDetectsGermanLanguage(): void
    {
        $request = $this->createRequest('/terminvereinbarung/api/citizen/de/services');
        $handler = $this->createHandler();

        $this->middleware->process($request, $handler);

        $this->assertEquals('de', $request->getAttribute('language'));
    }

    public function testDetectsEnglishLanguage(): void
    {
        $request = $this->createRequest('/terminvereinbarung/api/citizen/en/services');
        $handler = $this->createHandler();

        $this->middleware->process($request, $handler);

        $this->assertEquals('en', $request->getAttribute('language'));
    }

    public function testUsesDefaultLanguageWhenNoLanguageSpecified(): void
    {
        $request = $this->createRequest('/terminvereinbarung/api/citizen/services');
        $handler = $this->createHandler();

        $this->middleware->process($request, $handler);

        $this->assertEquals('de', $request->getAttribute('language'));
    }

    public function testUsesDefaultLanguageForInvalidLanguage(): void
    {
        $request = $this->createRequest('/terminvereinbarung/api/citizen/fr/services');
        $handler = $this->createHandler();

        $this->middleware->process($request, $handler);

        $this->assertEquals('de', $request->getAttribute('language'));
    }

    public function testNormalizeLanguageReturnsDefaultForInvalidLanguage(): void
    {
        $this->assertEquals('de', LanguageMiddleware::normalizeLanguage('fr'));
    }

    public function testNormalizeLanguageReturnsDefaultForNullLanguage(): void
    {
        $this->assertEquals('de', LanguageMiddleware::normalizeLanguage(null));
    }

    public function testNormalizeLanguagePreservesValidLanguage(): void
    {
        $this->assertEquals('en', LanguageMiddleware::normalizeLanguage('en'));
    }

    public function testGetSupportedLanguagesReturnsArray(): void
    {
        $languages = LanguageMiddleware::getSupportedLanguages();
        $this->assertIsArray($languages);
        $this->assertContains('de', $languages);
        $this->assertContains('en', $languages);
    }

    public function testGetDefaultLanguageReturnsGerman(): void
    {
        $this->assertEquals('de', LanguageMiddleware::getDefaultLanguage());
    }

    public function testGetFallbackLanguageReturnsEnglish(): void
    {
        $this->assertEquals('en', LanguageMiddleware::getFallbackLanguage());
    }

    private function createRequest(string $path): ServerRequestInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);
    
        $attributes = [];
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getAttribute')
            ->willReturnCallback(function ($name) use (&$attributes) {
                return $attributes[$name] ?? null;
            });
        $request->method('withAttribute')
            ->willReturnCallback(function ($name, $value) use ($request, &$attributes) {
                $attributes[$name] = $value;
                return $request;
            });
    
        return $request;
    }

    private function createHandler(): RequestHandlerInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);
        
        return $handler;
    }
}