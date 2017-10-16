<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

// Symfony Translation Classes
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;

class Language
{
    public static $supportedLanguages = array();

    public $current = '';

    protected $default = '';

    protected static $instance = null;

    /**
     * @var \Psr\Http\Message\RequestInterface $request;
     *
     */
    protected $request = null;

    public function __construct(RequestInterface $request, array $supportedLanguages)
    {
        if (! self::$instance) {
            $this->request = $request;
            self::$supportedLanguages = $supportedLanguages;
            $this->current = $this->getLanguageFromRequest();
            if (!$this->current) {
                $this->current = $this->getDefault();
            }
            $this->setTranslator();
            self::$instance = $this;
        }
    }

    public function getCurrent($lang = '')
    {
        return ($lang != '') ? $lang : $this->current;
    }

    public function getCurrentLocale($locale = '')
    {
        return self::$supportedLanguages[$this->getCurrent($locale)]['locale'];
    }

    public function setCurrentLocale($locale = '')
    {
        $locale = ('' == $locale) ? self::$supportedLanguages[$this->current]['locale'] : $locale;
        \setlocale(LC_ALL, $locale);
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

    protected function getLanguageFromRequest()
    {
        $current = null;
        $default = $this->getDefault();
        // Detect current language based on request URI
        $lang_ids = array_keys(self::$supportedLanguages);
        $lang_ids = array_diff($lang_ids, array($default));
        if (null !== $this->request) {
            $queryString = $this->request->getUri()->getQuery();
            parse_str($queryString, $queryArr);
            $current = isset($queryArr['lang']) ? $queryArr['lang'] : $this->getDefault();
        }
        return $current;
    }

    protected function setTranslator()
    {
        $default = $this->getCurrentLocale($this->getDefault());
        // First param is the "default language" to use.
        $translator = new Translator($this->getCurrentLocale($this->current), new MessageSelector());
        // Set a fallback language incase you don't have a translation in the default language
        $translator->setFallbackLocales([$default]);
        // Add a loader that will get the php files we are going to store our translations in
        $translator->addLoader('json', new JsonFileLoader());
        // Add language files here
        foreach (\App::$supportedLanguages as $language) {
            $locale = $language['locale'];
            $translator->addResource('json', \App::APP_PATH .'/lang/'. $locale .'.json', $locale);
        }

        if (! isset(self::$supportedLanguages[$this->current]['locale'])) {
            throw new \Exception("Unsupported type of language");
        }
        \BO\Slim\Bootstrap::addTwigExtension(new TranslationExtension($translator));
    }
}
