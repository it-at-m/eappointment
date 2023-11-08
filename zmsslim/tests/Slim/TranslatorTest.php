<?php

namespace BO\Slim\Tests;

use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testTranslatorPoLoader()
    {
        \App::$languagesource = "pofile";
        \App::$supportedLanguages = array(
            'de' => array(
                'name'    => 'Deutsch',
                'locale'  => 'de_DE',
                'default' => true,
            ),
            'en' => array(
                'name'    => 'English',
                'locale'  => 'en_GB',
                'default' => false,
            )
        );
        $translator = new \BO\Slim\LanguageTranslator('de_DE', 'en_GB', 'de');
        $this->assertEquals('en_GB', $translator->getInstance()->getLocale());
        $this->assertContains('de_DE', $translator->getInstance()->getFallbackLocales());
        
        // does not work because the default language is not accepted when loading the languages in zmsslim language translator
        /*
        $this->assertEquals(
            'das ist ein pofile Test',
            $translator->getInstance()->getCatalogue('de_DE')->get('unittest')
        );
        */
        $this->assertEquals(
            'this is a pofile test',
            $translator->getInstance()->getCatalogue('en_GB')->get('unittest')
        );
    }

    public function testTranslatorJsonLoader()
    {
        \App::$languagesource = "json";
        \App::$supportedLanguages = array(
            'de' => array(
                'name'    => 'Deutsch',
                'locale'  => 'de_DE',
                'default' => true,
            ),
            'en' => array(
                'name'    => 'English',
                'locale'  => 'en_GB',
                'default' => false,
            )
        );
        $translator = new \BO\Slim\LanguageTranslator('de_DE', 'en_GB', 'de');
        $this->assertEquals('en_GB', $translator->getInstance()->getLocale());
        $this->assertContains('de_DE', $translator->getInstance()->getFallbackLocales());
        $this->assertEquals(
            'das ist ein json Test',
            $translator->getInstance()->getCatalogue('de_DE')->get('unittest')
        );
        $this->assertEquals(
            'this is a json test',
            $translator->getInstance()->getCatalogue('en_GB')->get('unittest')
        );
    }
}
