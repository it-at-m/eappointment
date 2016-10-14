<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array())
    {
        $routePath = $this->getContainer()->router->pathFor($name, $params);
        $lang = (isset($params['lang'])) ? $params['lang'] : null;
        $routePath = $this->getWithNewLanguageInUri($routePath, $lang);
        return $routePath;
    }

    protected function getWithNewLanguageInUri($routePath, $newLanguage = null)
    {
        if ($newLanguage && \App::$language->getDefault() != \App::$language->getCurrent()) {
            if ($newLanguage != \App::$language->getDefault()) {
                $routePath = preg_replace('~^/('.\App::$language->getCurrent().')~', $newLanguage, $routePath);
            }
            if ($newLanguage == \App::$language->getDefault()) {
                $routePath = preg_replace('~^/('.\App::$language->getCurrent().')~', '', $routePath);
            }
        } else {
            if ($newLanguage && $newLanguage != \App::$language->getDefault()) {
                $routePath = sprintf('/%s%s', $newLanguage, $routePath);
            }
        }
        return $routePath;
    }
}
