<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;

class Language
{
    public static array $supportedLanguages = array();

    public string $current = '';

    protected string $currentLocale = '';

    protected string $default = '';

    protected static ?Translator $translatorInstance = null;

    public function __construct(RequestInterface $request, array $supportedLanguages)
    {
        self::$supportedLanguages = $supportedLanguages;
        $this->current = $this->getLanguageFromRequest($request);
        $fallbackLocale = $this->getLocale($this->getDefault());
        $this->currentLocale = $this->getLocale($this->getCurrentLanguage());
        $this->setCurrentLocale();
        $defaultLang = $this->getDefault();

        /** @psalm-suppress RedundantCondition Module App subclasses may set MULTILANGUAGE to false. */
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

    /** @psalm-api */
    public function getDefaultLanguageName(): ?string
    {
        $default = \App::$supportedLanguages[$this->getDefault()]['name'] ?? null;
        return $default;
    }

    public function getCurrentLanguage(string $lang = ''): string
    {
        $current = (isset(self::$supportedLanguages[$this->current])) ? $this->current : $this->getDefault();
        return ($lang != '') ? $lang : $current;
    }

    public function getLocale(string $locale = ''): string
    {
        $locale = ('' == $locale) ? $this->getDefault() : $locale;
        if (
            isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]) &&
            isset(self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'])
        ) {
            $locale = (string) self::$supportedLanguages[$this->getCurrentLanguage($locale)]['locale'];
        }
        return $locale;
    }

    /** @psalm-api */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale;
    }

    public function setCurrentLocale(): void
    {
        if (class_exists("Locale")) {
            \Locale::setDefault($this->currentLocale);
        }
        \setlocale(LC_ALL, $this->getLocaleList($this->currentLocale));
    }

    /**
     * @return string[]
     *
     */
    protected function getLocaleList(string $locale): array
    {
        $localeList = [];
        $localeList[] = $this->getCurrentLanguage();
        $localeList[] = $locale;
        $suffixList = ['utf8', 'utf-8'];
        foreach ($suffixList as $suffix) {
            array_unshift($localeList, $locale . '.' . $suffix);
        }
        return $localeList;
    }

    public function getDefault(): string
    {
        if ($this->default !== '') {
            return $this->default;
        }
        foreach (self::$supportedLanguages as $lang_id => $lang_data) {
            if (isset($lang_data['default']) && $lang_data['default']) {
                $this->default = (string) $lang_id;
                return $this->default;
            }
        }
        $first = array_key_first(self::$supportedLanguages);
        $this->default = $first !== null ? (string) $first : '';
        return $this->default;
    }

    // Detect current language based on request URI or Parameter
    protected function getLanguageFromRequest(RequestInterface $request): string
    {
        $language = $this->getLanguageFromUri($request);

        if ($request instanceof ServerRequestInterface) {
            $route = $request->getAttribute('route');
            if ($route instanceof \Slim\Routing\Route) {
                $lang = $route->getArgument('lang');
                if ($lang !== null && $lang !== '') {
                    $language = $lang;
                }
            }
        }

        return $language;
    }

    protected function getLanguageFromUri(RequestInterface $request): string
    {
        if ($request instanceof Request) {
            $requestParamLang = $request->getParam('lang');
            return ($requestParamLang) ? (string) $requestParamLang : $this->getDefault();
        }
        if ($request instanceof ServerRequestInterface) {
            $query = $request->getQueryParams();
            return !empty($query['lang']) ? (string) $query['lang'] : $this->getDefault();
        }
        return $this->getDefault();
    }
}
