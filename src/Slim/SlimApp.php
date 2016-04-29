<?php

namespace BO\Slim;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array(), $lang = null)
    {
        $currentLang = (null !== $lang) ? $lang : \App::$locale;
        $params['lang'] = (isset($params['lang'])) ? $params['lang'] : $currentLang;
        if ($params['lang'] == '' || $params['lang'] == \App::DEFAULT_LANG) {
            unset($params['lang']);
        }
        return $this->getContainer()->router->pathFor($name, $params);
    }
}
