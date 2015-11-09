<?php

namespace BO\Slim;

class I18nSlim extends \Slim\Slim
{
    public function urlFor($name, $params = array(), $lang = null)
    {
        $currentLang = (null !== $lang) ? $lang : $this->config('lang');
        $params['lang'] = (isset($params['lang'])) ? $params['lang'] : $currentLang;
        if ($params['lang'] == '' || $params['lang'] == \App::DEFAULT_LANG) {
            unset($params['lang']);
        }
        return parent::urlFor($name, $params);
    }
}
