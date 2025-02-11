<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;
// Symfony Translation Classes
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

class LanguageTranslator
{
    protected $translator = null;

    protected $defaultLang;

    protected $loaderTypes = [
        'pofile' => 'setPoFileLoader',
        'json' => 'setJsonFileLoader'
    ];

    public function __construct($fallbackLocale, $defaultLocale, $defaultLang)
    {
        $translatorType = (\App::$languagesource) ? \App::$languagesource : 'pofile';

        $this->defaultLang = $defaultLang;

        $translatorClass = \APP::TRANSLATOR_CLASS;
        // First param is the "default language" to use.
        $this->translator = new $translatorClass($defaultLocale);
        // Set a fallback language incase you don't have a translation in the default language
        $this->translator->setFallbackLocales([$fallbackLocale]);
        // Add a loader that will get the php files we are going to store our translations in
        $initLoader = $this->loaderTypes[$translatorType];
        $this->$initLoader();
    }

    public function getInstance()
    {
        return $this->translator;
    }

    protected function setJsonFileLoader()
    {
        $this->translator->addLoader('json', new JsonFileLoader());
        foreach (\App::$supportedLanguages as $language) {
            $this->translator->addResource(
                'json',
                \App::APP_PATH . '/lang/' . $language['locale'] . '.json',
                $language['locale']
            );
        }
    }

    protected function setPoFileLoader()
    {
        $this->translator->addLoader('pofile', new PoFileLoader());
        foreach (\App::$supportedLanguages as $locale => $language) {
            if ($locale != $this->defaultLang) {
                $this->translator->addResource(
                    'pofile',
                    \App::APP_PATH . '/lang/' . $language['locale'] . '.po',
                    $language['locale']
                );
            }
        }
    }
}
