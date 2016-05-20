<?php

namespace BO\Slim;

class Language
{

    public static $languages = array();

    public static $current = '';
    public static $default = '';

    public static function getLanguage()
    {
        if (empty(self::$languages)) {
            return false;
        }

        self::getDefault();

        // Detect current language based on request URI
        $lang_ids = array_keys(self::$languages);
        $lang_ids = array_diff($lang_ids, array(self::$default));
        if (null !== \App::$slim->router()->getCurrentRoute()) {
            $url = \App::$slim->request()->getResourceUri();
            if (preg_match('~^/('.implode('|', $lang_ids).')/~', $url, $matches)) {
                self::$current = $matches[1];
            } else {
                self::$current = self::$default;
            }
        } else {
            self::$current = self::$default;
        }

        self::setTextDomain();
    }

    public static function getCurrent($lang)
    {
        return ($lang != '') ? $lang : self::$current;
    }

    protected static function getDefault()
    {
        // Find default language
        if (! self::$default) {
            foreach (self::$languages as $lang_id => $lang_data) {
                if (isset($lang_data['default']) && $lang_data['default']) {
                    self::$default = $lang_id;
                    break;
                }
            }
            if (! self::$default) {
                reset(self::$languages);
                self::$default = key(self::$languages);
            }
        }
    }

    protected static function setTextDomain()
    {
        $domain = 'dldb-'.self::$current;
        \putenv('LANG='. self::$current);
        \setlocale(LC_ALL, self::$languages[self::$current]['locale']);
        // Specify the location of the translation tables
        \bindtextdomain($domain, \App::APP_PATH. '/locale');
        \bind_textdomain_codeset($domain, \App::CHARSET);
        textdomain($domain);
    }
}
