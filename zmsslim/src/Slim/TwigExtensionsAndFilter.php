<?php

/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

/**
  * Extension for Twig and Slim
  *
  */
class TwigExtensionsAndFilter extends TwigExtension
{
    public function getFilters()
    {
        return array(
            new \Twig\TwigFilter('msort', array($this, 'msort')),
            new \Twig\TwigFilter('getObjectName', array($this, 'getObjectName')),
            new \Twig\TwigFilter('decodeEntities', array($this, 'decodeEntities')),
            new \Twig\TwigFilter('sanitize_html', array($this, 'sanitizeHtml'), ['is_safe' => ['html']])
        );
    }

    public function decodeEntities($string)
    {
        return $string === null ? '' : trim(nl2br(html_entity_decode($string)));
    }

    public function msort($array, $key, $sort_flags = SORT_REGULAR)
    {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

    public function getObjectName($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    public function sanitizeHtml($html)
    {
        static $purifier = null;
        if ($purifier === null) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
        }
        return $purifier->purify($html);
    }
}
