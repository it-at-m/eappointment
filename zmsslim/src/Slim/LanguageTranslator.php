<?php

namespace BO\Slim;

// Symfony Translation Classes
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

class LanguageTranslator
{
    protected Translator $translator;

    protected string $defaultLang;

    /** @var array{json: string, pofile: string} */
    protected array $loaderTypes = [
        'pofile' => 'setPoFileLoader',
        'json' => 'setJsonFileLoader'
    ];

    public function __construct(string $fallbackLocale, string $defaultLocale, string $defaultLang)
    {
        $translatorType = (\App::$languagesource) ? \App::$languagesource : 'pofile';

        $this->defaultLang = $defaultLang;

        /** @var class-string<Translator> $translatorClass */
        $translatorClass = \App::TRANSLATOR_CLASS;
        // First param is the "default language" to use.
        /** @psalm-suppress UnsafeInstantiation */
        $this->translator = new $translatorClass($defaultLocale);
        // Set a fallback language incase you don't have a translation in the default language
        $this->translator->setFallbackLocales([$fallbackLocale]);
        // Add a loader that will get the php files we are going to store our translations in
        $initLoader = $this->loaderTypes[$translatorType] ?? 'setPoFileLoader';
        $this->$initLoader();
    }

    public function getInstance(): Translator
    {
        return $this->translator;
    }

    /** @psalm-api */
    protected function setJsonFileLoader(): void
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

    /** @psalm-api */
    protected function setPoFileLoader(): void
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
