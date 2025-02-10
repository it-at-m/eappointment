<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

class Language
{
    public static $supportedLanguages = array();

    public $current = '';

    protected $currentLocale = '';

    protected $default = '';

    protected static $translatorInstance = null;

    public function __construct(RequestInterface $request, array $supportedLanguages)
    {
        self::$supportedLanguages = $supportedLanguages;
        $this->current = $this->getLanguageFromRequest($request);
        $fallbackLocale = $this->getLocale($this->getDefault());
        $this->currentLocale = $this->getLocale($this->getCurrentLanguage());
        $this->setCurrentLocale();
        $defaultLang = $this->getDefault();

        if (
            \App::MULTILANGUAGE
            || (strlen($fallbackLocale) > 0 && strlen($this->currentLocale) > 0 && strlen($defaultLang) > 0)
        ) {
            if (null === self::$translatorInstance) {
                self::$translatorInstance = (new LanguageTranslator(
                    $fallbackLocale,
                    $this->currentLocale,
                    $defaultLang
                ))->getInstance();
                \BO\Slim\Bootstrap::addTwigExtension(new TranslationExtension(self::$translatorInstance));
            } else {
                self::$translatorInstance->setLocale($this->currentLocale);
            }
        }
    }

    public function getDefaultLanguageName()
    {
        $default = \App::$supportedLanguages[$this->getDefault()]['name'] ?? null;
        return $default;
    }

    public function getCurrentLanguage($lang = '')
    {
        $current = (isset(self::$supportedLanguages[$this->current])) ? $this->current : $this->getDefault();
        return ($lang != '') ? $lang : $current;
    }

    public function getLocale($locale = '')
    {
        $locale = ('' == $locale) ? $this->getDefault() : $locale;
        if (
            isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]) &&
            isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'])
        ) {
            $locale = self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'];
        }
        return $locale;
    }

    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    public function setCurrentLocale()
    {
        if (class_exists("Locale")) {
            \Locale::setDefault($this->currentLocale);
        }
        \setlocale(LC_ALL, $this->getLocaleList($this->currentLocale));
    }

    protected function getLocaleList($locale)
    {
        $localeList[] = $this->getCurrentLanguage();
        $localeList[] = $locale;
        $suffixList = ['utf8', 'utf-8'];
        foreach ($suffixList as $suffix) {
            array_unshift($localeList, $locale . '.' . $suffix);
        }
        return $localeList;
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

    // Detect current language based on request URI or Parameter
    protected function getLanguageFromRequest(RequestInterface $request)
    {
        $language = $this->getLanguageFromUri($request);
        $route = $request->getAttribute('route');

        if ($route instanceof \Slim\Routing\Route) {
            $lang = $route->getArgument('lang');
            $language = (!empty($lang)) ? $lang : $language;
        }

        return $language;
    }

    protected function getLanguageFromUri($request)
    {
        $requestParamLang = $request->getParam('lang');
        return ($requestParamLang) ? $requestParamLang : $this->getDefault();
    }
}
