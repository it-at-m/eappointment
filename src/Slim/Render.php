<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

class Render
{

    /**
     * @return self
     */
    public static function error404()
    {
        \App::$slim->notFound();
    }

    /**
     * @return NULL
     */
    public static function html($template, $parameters = array(), $status = 200)
    {
        \App::$slim->response->setStatus($status);
        \App::$slim->response->headers->set('Content-Type', 'text/html; charset=utf-8');
        \App::$slim->render($template, $parameters);
    }

    /**
     * @return NULL
     */
    public static function json($data, $status = 200)
    {
        \App::$slim->response->setStatus($status);
        \App::$slim->response->headers->set('Content-Type', 'application/json');
        \App::$slim->response->setBody(json_encode($data));
    }

    /**
     * @param String $date strtotime interpreted
     * @param String $expires strtotime interpreted
     *
     * @return NULL
     */
    public static function lastModified($date, $expires = '+5 minutes')
    {
        if (!is_int($date)) {
            $date = strtotime($date);
        }
        \App::$slim->lastModified($date);
        \App::$slim->expires($expires);
    }

    /**
     * @param String $route_name
     * @param Array $arguments parameters in the route path
     * @param Array $parameter parameters to append with "?"
     * @param Int $statuscode see an HTTP reference
     *
     * @return NULL
     */
    public static function redirect($route_name, $arguments, $parameter, $statuscode = 302)
    {
        $url = \App::$slim->urlFor($route_name, $arguments);
        $url = Helper::proxySanitizeUri($url);
        $url = preg_replace('#^.*?(https?://)#', '\1', $url); // allow http:// routes
        if ($parameter) {
            $url .= '?' . http_build_query($parameter);
        }
        \App::$slim->redirect($url, $statuscode);
        return $url;
    }
}
