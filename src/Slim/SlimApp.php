<?php

namespace BO\Slim;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array())
    {
        $routePath = $this->getContainer()->router->pathFor($name, $params);
        $lang = (\App::$language->getCurrent() != \App::$language->getDefault()) ? \App::$language->getCurrent() : '';
        if ($lang != \App::$language->getCurrent()) {
            $routePath = preg_replace('~^/('.\App::$language->getCurrent().')/~', $lang, $routePath);
            //$routePath = sprintf('/%s%s', '', $routePath);
        }
        return $routePath;
    }
}
