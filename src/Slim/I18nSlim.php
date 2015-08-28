<?php

namespace BO\Slim;

class I18nSlim extends \Slim\Slim
{
    public function urlFor($name, $params = array(), $lang = null)
    {
        $lang = $lang ? $lang : $this->config('lang');
        $params['lang'] = (isset($params['lang'])) ? $params['lang'] : $lang;
        if ($params['lang'] == '' || $params['lang'] == 'de') {
            unset($params['lang']);
        }
        
        return parent::urlFor($name, $params);
    }
}
