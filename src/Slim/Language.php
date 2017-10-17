<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

// Symfony Translation Classes
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

class Language
{
    public static $supportedLanguages = array();

    public $current = '';

    protected $default = '';

    protected static $translator = null;

    /**
     * @var \Psr\Http\Message\RequestInterface $request;
     *
     */
    protected $request = null;

    public function __construct(RequestInterface $request, array $supportedLanguages)
    {
        $this->request = $request;
        self::$supportedLanguages = $supportedLanguages;
        $this->current = $this->getLanguageFromRequest();
        if (!$this->current) {
            $this->current = $this->getDefault();
        }
        $this->setCurrentLocale();
        if (! self::$translator) {
            self::$translator = $this->setTranslator();
        }
    }

    public function getCurrentLanguage($lang = '')
    {
        return ($lang != '') ? $lang : $this->current;
    }

    public function getCurrentLocale($locale = '')
    {
        return self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'];
    }

    public function getDefault()
    {
        if (! $this->default) {
            foreach (self::$supportedLanguages as $lang_id => $lang_data) {
                if (isset($lang_data['default']) && $lang_data['default']) {
                    $this->default = $lang_id;
                    break;
                }
            }
            if (! $this->default) {
                reset(self::$supportedLanguages);
                $this->default = key(self::$supportedLanguages);
            }
        }
        return $this->default;
    }

    public function setCurrentLocale($locale = '')
    {
        $locale = ('' == $locale) ? self::$supportedLanguages[$this->current]['locale'] : $locale;
        \setlocale(LC_ALL, $locale);
    }

    // Detect current language based on request URI or Parameter
    protected function getLanguageFromRequest()
    {
        $current = null;
        if (null !== $this->request) {
            $current = $this->getLanguageFromUri();
            if (! $current) {
                $requestParamLang = $this->request->getParam('lang');
                $current = ($requestParamLang) ? $requestParamLang : $this->getDefault();
            }
        }
        return $current;
    }

    protected function getLanguageFromUri()
    {
        $queryString = $this->request->getUri()->getQuery();
        parse_str($queryString, $queryArr);
        return isset($queryArr['lang']) ? $queryArr['lang'] : null;
    }

    protected function setTranslator()
    {
        $default = $this->getCurrentLocale($this->getDefault());
        $current = $this->getCurrentLanguage();
        // First param is the "default language" to use.
        $translator = new Translator($this->getCurrentLocale($current), new MessageSelector());
        // Set a fallback language incase you don't have a translation in the default language
        $translator->setFallbackLocales([$default]);
        // Add a loader that will get the php files we are going to store our translations in
        $translator->addLoader('json', new JsonFileLoader());
        // Add language files here
        foreach (\App::$supportedLanguages as $language) {
            $locale = $language['locale'];
            $translator->addResource('json', \App::APP_PATH .'/lang/'. $locale .'.json', $locale);
        }
        if (! isset(self::$supportedLanguages[$current]['locale'])) {
            throw new \Exception("Unsupported type of language");
        }
        \BO\Slim\Bootstrap::addTwigExtension(new TranslationExtension($translator));
        return $translator;
    }
}
