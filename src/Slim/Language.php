<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

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
        if (self::$instance) {
            throw new Exception('\BO\Slim\Language is a singleton, do not init twice');
        }
        self::$instance = $this;

        $this->request = $request;
        self::$supportedLanguages = $supportedLanguages;
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
        $default = $this->getDefault();

        // Detect current language based on request URI
        $lang_ids = array_keys(self::$supportedLanguages);
        $lang_ids = array_diff($lang_ids, array($default));
        if (null !== $this->request) {
            $url = $this->request->getUri()->getPath();
            if (preg_match('~^('.implode('|', $lang_ids).')/~', $url, $matches)) {
                $this->current = $matches[1];
            } else {
                $this->current = $default;
            }
        } else {
            $this->current = $default;
        }

        $this->setTextDomain();
    }

    public function getCurrent($lang = '')
    {
        return ($lang != '') ? $lang : $this->current;
    }

    public function getCurrentLocale($lang = '')
    {
        return ($lang != '') ? $lang : $this->current;
    }

    public function getDefault()
    {
        // Find default language
        return $this->default;
    }

    protected function setTextDomain()
    {
        $domain = 'dldb-'.$this->current;
        \putenv('LANG='. $this->current);
        if (!isset(self::$supportedLanguages[$this->current]['locale'])) {
            throw new \Exception("Unsupported type of language");
        }
        $locale = self::$supportedLanguages[$this->current]['locale'];
        \setlocale(LC_ALL, $locale);
        // Specify the location of the translation tables
        \bindtextdomain($domain, \App::APP_PATH. '/locale');
        \bind_textdomain_codeset($domain, \App::CHARSET);
        textdomain($domain);
    }
}
