<?php

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\TemplateUrls;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\UriFactory;

class TemplateUrlsTest extends TestCase
{
    protected function tearDown(): void
    {
        \App::$includeUrl = null;
        parent::tearDown();
    }

    public function testUsesApplicationIncludeUrlWhenSet()
    {
        \App::$includeUrl = '/terminvereinbarung/admin';
        $request = $this->createRequest('/terminvereinbarung/admin/status/');

        $this->assertSame('/terminvereinbarung/admin', TemplateUrls::resolveIncludeUrl($request));
        $this->assertSame('/terminvereinbarung/admin', TemplateUrls::resolveBaseUrl($request));
    }

    public function testResolvesFromRequestWhenApplicationIncludeUrlIsUnset()
    {
        $request = $this->createRequest('/terminvereinbarung/admin/status/');

        $this->assertStringContainsString('/terminvereinbarung/admin', TemplateUrls::resolveIncludeUrl($request));
        $this->assertNotSame('', TemplateUrls::resolveBaseUrl($request));
    }

    private function createRequest(string $path): Request
    {
        $uri = (new UriFactory())->createUri('https://localhost' . $path);
        $serverRequest = (new ServerRequestFactory())->createServerRequest('GET', $uri)
            ->withUri($uri)
            ->withServerParams([
                'REQUEST_URI' => $path,
                'SCRIPT_NAME' => '/index.php',
            ]);

        return new Request($serverRequest);
    }
}
