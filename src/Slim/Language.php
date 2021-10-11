<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

use Symfony\Bridge\Twig\Extension\TranslationExtension;

class Language
{
    public static $supportedLanguages = array();

    public $current = '';

    protected $default = '';

    protected static $translatorInstance = null;

    public function __construct(RequestInterface $request, array $supportedLanguages)
    {
        self::$supportedLanguages = $supportedLanguages;
        $this->current = $this->getLanguageFromRequest($request);
        $this->setCurrentLocale();
        $fallbackLocale = $this->getCurrentLocale($this->getDefault());
        $defaultLocale = $this->getCurrentLocale($this->getCurrentLanguage());
        $defaultLang = $this->getDefault();
        
        if (null == self::$translatorInstance) {
            self::$translatorInstance = (new LanguageTranslator(
                $fallbackLocale, 
                $defaultLocale, 
                $defaultLang
            ))->getInstance();
            \BO\Slim\Bootstrap::addTwigExtension(new TranslationExtension(self::$translatorInstance));
        } else {
            self::$translatorInstance->setLocale($this->current);
        }
    
    }

    public function getCurrentLanguage($lang = '')
    {
        $current = (isset(self::$supportedLanguages[$this->current])) ? $this->current : $this->getDefault();
        return ($lang != '') ? $lang : $current;
    }

    public function getCurrentLocale($locale = '')
    {
        if (isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]) &&
            isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'])) {
            $locale = self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'];
        } else {
            $locale = $this->getDefault();
        }
        return $locale;
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
        if (isset(self::$supportedLanguages[$this->getCurrentLanguage()]) &&
            isset(self::$supportedLanguages[$this->getCurrentLanguage()]['locale'])) {
            $locale = self::$supportedLanguages[$this->getCurrentLanguage()]['locale'];
        } elseif ('' == $locale) {
            $locale = $this->getDefault();
        }
        if (class_exists("Locale")) {
            \Locale::setDefault($locale);
        }
        \setlocale(LC_ALL, $this->getLocaleList($locale));
    }

    protected function getLocaleList($locale)
    {
        $localeList[] = $this->getCurrentLanguage();
        $localeList[] = $locale;
        $suffixList = ['utf8', 'utf-8'];
        foreach ($suffixList as $suffix) {
            array_unshift($localeList, $locale .'.'. $suffix);
        }
        return $localeList;
    }

    // Detect current language based on request URI or Parameter
    protected function getLanguageFromRequest($request = null)
    {
        $current = null;
        if (null !== $request) {
            $route = $request->getAttribute('route');
            if (null !== $route) {
                $lang = $route->getArgument('lang');
                $current = (!empty($lang)) ? $lang : $current;
            }   
        }
        return ($current) ? $current : $this->getLanguageFromUri($request);
    }

    protected function getLanguageFromUri($request)
    {
        $requestParamLang = $request->getParam('lang');
        return ($requestParamLang) ? $requestParamLang : $this->getDefault();
    }
}
