<?php

namespace BO\Slim\Tests;

class GetTest extends Base
{

    protected $classname = "Get";
    protected $arguments = [ ];

    protected $parameters = [
        'message' => 'this is a GET test message'
    ];

    protected $sessionData = [ ];

    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters, $this->sessionData);
        $this->assertStringContainsString('this is a GET test message', (string) $response->getBody());
        $this->assertStringContainsString('GET test title', (string) $response->getBody());
        $this->assertEquals('de', \App::$language->getCurrentLanguage());

        //retry to test static::$translatorInstance not null
        $response = $this->render($this->arguments, $this->parameters, $this->sessionData);
        $this->assertEquals('de', \App::$language->getCurrentLanguage());
        $this->assertEquals('de', $this->getTwigExtensions()->currentLang());
        $this->assertEquals('de_DE', $this->getTwigExtensions()->currentLocale());
    }

    public function testWithLanguageFromRoute()
    {
        $routeCollector = \App::$slim->getRouteCollector();
        $route = $routeCollector->map(
            ['GET'],
            '/unittest/{id}/{lang}/',
            []
        );
        $route->setArguments(['id' => 123, 'lang' => 'en']);
        
        $response = $this->render($this->arguments, [
            '__route' => $route
        ], $this->sessionData);
        $this->assertStringContainsString('language: en', (string) $response->getBody());
        $this->assertEquals('en', \App::$language->getCurrentLanguage());
        $this->assertEquals('en', $this->getTwigExtensions()->currentLang());
        $this->assertEquals('en_GB', $this->getTwigExtensions()->currentLocale());
    }

    public function testLanguageWithoutDefault()
    {
        \App::$supportedLanguages = array(
            // Default language
            'de' => array(
                'name'    => 'Deutsch',
                'locale'  => 'de_DE.utf-8',
                'default' => false,
            ),
            'en' => array(
                'name'    => 'English',
                'locale'  => 'en_GB.utf-8',
                'default' => false,
            )
        );
        $response = $this->render($this->arguments, $this->parameters, $this->sessionData);
        $this->assertEquals('de', \App::$language->getCurrentLanguage());
        $this->assertEquals('de_DE', $this->getTwigExtensions()->currentLocale());
    }

    public function testMethodNotAllowed()
    {
        $request = self::createBasicRequest('GET', '/unittest/');
        $response = \App::$slim->handle($request);
        $response->getBody()->rewind();
        $this->assertStringContainsString(
            'Method not allowed. Must be one of: POST',
            $response->getBody()->getContents()
        );
    }

    public function testNotFound()
    {
        $request = self::createBasicRequest('GET', '/notfound/');
        $response = \App::$slim->handle($request);
        $response->getBody()->rewind();

        $this->assertStringContainsString('Not Found', $response->getBody()->getContents());
    }
}
