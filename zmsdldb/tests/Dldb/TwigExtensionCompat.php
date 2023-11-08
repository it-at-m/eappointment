<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Tests;

/**
  * Extension for Twig and Slim
  *
  */
class TwigExtensionCompat extends \Twig_Extension
{

    public function getName()
    {
        return 'dldb-unittest';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('urlGet', array($this, 'urlGet')),
            new \Twig_SimpleFunction('remoteInclude', array($this, 'remoteInclude')),
            new \Twig_SimpleFunction('includeUrl', array($this, 'includeUrl')),
            new \Twig_SimpleFunction('baseUrl', array($this, 'includeUrl')),
        );
    }

    public function urlGet($routeName, $params = array(), $getparams = array())
    {
        return "#$routeName(".implode(',', $params)."):".json_encode($getparams);
    }

    public function includeUrl($withUri = true)
    {
        //error_log("TODO: Remove includeUrl and baseUrl functions and use template parameters");
        return "/";
    }

    public static function remoteInclude($uri)
    {
        $prepend = "<!-- include($uri) -->\n";
        $append = "\n<!-- /include($uri) -->";
        // Varnish does not support https
        $uri = preg_replace('#^(https?:)?//#', 'http://', $uri);
        if (\App::SLIM_DEBUG) {
            $prepend = "<!-- replaced uri=$uri --> " . $prepend;
        }
        return $prepend . '<esi:include src="' . $uri . '" />' . $append;
    }
}
