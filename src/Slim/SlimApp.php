<?php

namespace BO\Slim;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array(), $lang = null)
    {
        $currentLang = (null !== $lang) ? $lang : \App::$language->getCurrent();
        $params['lang'] = (isset($params['lang'])) ? $params['lang'] : $currentLang;
        if ($params['lang'] == '' || $params['lang'] == \App::$language->getDefault()) {
            unset($params['lang']);
        }
        return $this->getContainer()->router->pathFor($name, $params);
    }
}
