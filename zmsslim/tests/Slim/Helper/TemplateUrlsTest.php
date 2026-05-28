<?php

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\TemplateUrls;
use BO\Slim\Request;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Headers;
use Slim\Psr7\Stream;
use Slim\Psr7\Uri;

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

        $this->assertSame('/terminvereinbarung/admin', TemplateUrls::resolveIncludeUrl($request));
        $this->assertSame('/terminvereinbarung/admin', TemplateUrls::resolveBaseUrl($request));
    }

    private function createRequest(string $requestUri): Request
    {
        $uri = new Uri('https', 'localhost', 80, $requestUri);
        $serverParams = [
            'REQUEST_URI' => $requestUri,
            'SCRIPT_NAME' => '/terminvereinbarung/admin/index.php',
        ];

        return new Request(
            'GET',
            $uri,
            new Headers([]),
            [],
            $serverParams,
            new Stream(fopen('php://temp', 'wb+'))
        );
    }
}
