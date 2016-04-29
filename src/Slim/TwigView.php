<?php

namespace BO\Slim;

class TwigView extends \Slim\Views\Twig
{
    /**
     * @var TwigEnvironment The Twig environment for rendering templates.
     */
    private $parserInstance = null;

    public function getInstance()
    {
        if (!$this->parserInstance) {
            $loader = new \Twig_Loader_Filesystem(array($this->getTemplatesDirectory()));
            $this->parserInstance = new \Twig_Environment(
                $loader,
                $this->parserOptions
            );

            foreach ($this->parserExtensions as $ext) {
                $extension = is_object($ext) ? $ext : new $ext;
                $this->parserInstance->addExtension($extension);
            }
        }
        return $this->parserInstance;
    }
}
