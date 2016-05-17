<?php

namespace BO\Slim;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array(), $lang = null)
    {
        $currentLang = (null !== $lang) ? $lang : Language::$current;
        $params['lang'] = (isset($params['lang'])) ? $params['lang'] : $currentLang;
        if ($params['lang'] == '' || $params['lang'] == Language::$default) {
            unset($params['lang']);
        }
        return $this->getContainer()->router->pathFor($name, $params);
    }
}
