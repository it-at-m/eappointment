<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

// Symfony Translation Classes
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

class LanguageTranslator
{
    protected $translator = null;

    protected $defaultLang;

    protected $loaderTypes = [
        'pofile' => 'setPoFileLoader',
        'json' => 'setJsonFileLoader'
    ];

    public static function setTranslator($fallbackLocale, $defaultLocale, $defaultLang)
    {
        $translatorType = (\App::LANGUAGESOURCE) ? \App::LANGUAGESOURCE : 'pofile';
        $instance = new static();
        $instance->defaultLang = $defaultLang;
        // First param is the "default language" to use.
        $instance->translator = new Translator($defaultLocale);
        // Set a fallback language incase you don't have a translation in the default language
        $instance->translator->setFallbackLocales([$fallbackLocale]);
        // Add a loader that will get the php files we are going to store our translations in
        $initLoader = $instance->loaderTypes[$translatorType];
        $instance->$initLoader();
        \BO\Slim\Bootstrap::addTwigExtension(new TranslationExtension($instance->getTranslator()));
        return $instance;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    protected function setJsonFileLoader()
    {
        $this->translator->addLoader('json', new JsonFileLoader());
        foreach (\App::$supportedLanguages as $language) {
            $locale = $language['locale'];
            $this->translator->addResource('json', \App::APP_PATH .'/lang/'. $locale .'.json', $locale);
        }
    }

    protected function setPoFileLoader()
    {
        $this->translator->addLoader('pofile', new PoFileLoader());
        foreach (\App::$supportedLanguages as $locale => $language) {
            if ($locale != $this->defaultLang) {
                $this->translator->addResource(
                    'pofile',
                    \App::APP_PATH .'/lang/'. $locale .'.po',
                    $language['locale']
                );
            }
        }
    }
}
