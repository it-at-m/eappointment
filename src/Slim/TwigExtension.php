<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use Twig\Extension\AbstractExtension;

/**
  * Extension for Twig and Slim
  *
  *  @SuppressWarnings(PublicMethod)
  *  @SuppressWarnings(TooManyMethods)
  *  @SuppressWarnings(Coupling)
  *  @SuppressWarnings(Complexity)
  */
class TwigExtension extends AbstractExtension
{
    /**
     * @var \BO\Slim\Container
     */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'boslimExtension';
    }

    public function getFunctions()
    {
        $safe = array('is_safe' => array('html'));
        return array(
            new \Twig\TwigFunction('urlGet', array($this, 'urlGet')),
            new \Twig\TwigFunction('csvProperty', array($this, 'csvProperty')),
            new \Twig\TwigFunction('azPrefixList', array($this, 'azPrefixList')),
            new \Twig\TwigFunction('azPrefixListCollator', array($this, 'azPrefixListCollator')),
            new \Twig\TwigFunction('isValueInArray', array($this, 'isValueInArray')),
            new \Twig\TwigFunction('remoteInclude', array($this, 'remoteInclude'), $safe),
            new \Twig\TwigFunction('includeUrl', array($this, 'includeUrl')),
            new \Twig\TwigFunction('getEsiFromPath', array($this, 'getEsiFromPath')),
            new \Twig\TwigFunction('baseUrl', array($this, 'baseUrl')),
            new \Twig\TwigFunction('getLanguageDescriptor', array($this, 'getLanguageDescriptor')),
            new \Twig\TwigFunction('currentLang', array($this, 'currentLang')),
            new \Twig\TwigFunction('currentRoute', array($this, 'currentRoute')),
            new \Twig\TwigFunction('currentLocale', array($this, 'currentLocale')),
            new \Twig\TwigFunction('currentVersion', array($this, 'currentVersion')),
            new \Twig\TwigFunction('formatDateTime', array($this, 'formatDateTime')),
            new \Twig\TwigFunction('toTextFormat', array($this, 'toTextFormat')),
            new \Twig\TwigFunction('getNow', array($this, 'getNow')),
            new \Twig\TwigFunction('isNumeric', array($this, 'isNumeric')),
            new \Twig\TwigFunction('dumpAppProfiler', array($this, 'dumpAppProfiler'), $safe),
            new \Twig\TwigFunction('getSystemStatus', array($this, 'getSystemStatus'), $safe),
            new \Twig\TwigFunction('getClientHost', array($this, 'getClientHost')),
            new \Twig\TwigFunction('kindOfPayment', array($this, 'kindOfPayment')),
            new \Twig\TwigFunction('isImageAllowed', array($this, 'isImageAllowed')),
        );
    }


    public static function isImageAllowed()
    {
        return (isset(\App::$isImageAllowed)) ? \App::$isImageAllowed : true;
    }

    public function getLanguageDescriptor($locale = 'de')
    {
        $language = \App::$supportedLanguages[$locale] ?? [];
        return $language['name'] ?? null;
    }

    public static function isNumeric($var)
    {
        return is_numeric($var);
    }

    public static function getNow()
    {
        if (\App::$now instanceof \DateTimeInterface) {
            return \App::$now;
        }
        return new \DateTimeImmutable();
    }

    public static function getSystemStatus($env)
    {
        return getenv($env);
    }

    public function toTextFormat($string)
    {
        $string = \strip_tags($string, '<br />');
        $temp = str_replace(array("<br />"), "\n", $string);
        $lines = explode("\n", $temp);
        $new_lines = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $new_lines[]=trim($line);
            }
        }
        $result = implode("\n", $new_lines);
        return addSlashes($result);
    }

    public function formatDateTime($dateString)
    {
        $dateTime = new \DateTimeImmutable(
            $dateString->year.'-'.$dateString->month.'-'.$dateString->day,
            new \DateTimezone('Europe/Berlin')
        );
        $formatDate['date']     = Helper::getFormatedDates($dateTime, "EE, dd. MMMM yyyy");
        $formatDate['fulldate'] = Helper::getFormatedDates($dateTime, "EEEE, 'den' dd. MMMM yyyy");
        $formatDate['weekday']  = (date('w', $dateTime->getTimestamp()) == 0) ?
            date('w', $dateTime->getTimestamp()) + 6 :
            date('w', $dateTime->getTimestamp()) - 1;
        $formatDate['ym']       = $dateTime->format('Y-m');
        $formatDate['ymd']       = $dateTime->format('Y-m-d');
        $formatDate['ts']       = $dateTime->getTimestamp();
        $formatDate['time']     = ($dateTime->format('H:i') != '00:00') ?
            Helper::getFormatedDates($dateTime, 'HH:mm Uhr') :
            false;
        return $formatDate;
    }

    public function currentRoute($lang = null)
    {
        $route = array(
            'name' => 'noroute',
            'params' => []
        );
        if ($this->container->has('currentRoute')) {
            $routeParams = $this->container->get('currentRouteParams');
            if (null !== $lang && 'de' == $lang) {
                unset($routeParams['lang']);
            } elseif (\App::MULTILANGUAGE) {
                $routeParams['lang'] = ($lang !== null) ? $lang : \App::$language->getCurrentLanguage();
            }
            
            $routeName = $this->container->get('currentRoute');
            $route = array(
                'name' => $routeName,
                'params' => $routeParams
            );
        }
        return $route;
    }

    public function currentLang()
    {
        return (\App::MULTILANGUAGE) ? \App::$language->getCurrentLanguage() : 'de';
    }

    public function currentLocale()
    {
        $locale = 'de_DE';
        if (\App::MULTILANGUAGE) {
            $locale = explode('.', \App::$language->getCurrentLocale());
            $locale = reset($locale);
        }
        return $locale;
    }

    public function currentVersion()
    {
        $version = Version::getString();
        return ($version != Version::UNKNOWN) ? $version : Git::readCurrentVersion();
    }

    public function urlGet($routeName, $params = array(), $getparams = array())
    {
        $url = \App::$slim->urlFor($routeName, $params);
        $url = preg_replace('#^.*?(https?://)#', '\1', $url); // allow http:// routes
        if ($getparams) {
            $url .= '?' . http_build_query($getparams);
        }
        return Helper::proxySanitizeUri($url);
    }

    public function csvProperty($list, $property)
    {
        $propertylist = array();
        foreach ($list as $item) {
            if (!is_scalar($item) && array_key_exists($property, $item)) {
                $propertylist[] = $item[$property];
            }
        }
        return implode(',', array_unique($propertylist));
    }

    public function azPrefixList($list, $property)
    {
        $azList = array();
        foreach ($list as $item) {
            if (!is_scalar($item) && array_key_exists($property, $item)) {
                $currentPrefix = self::sortFirstChar($item[$property]);
                if (!array_key_exists($currentPrefix, $azList)) {
                    $azList[$currentPrefix] = array(
                        'prefix' => $currentPrefix,
                        'sublist' => array(),
                    );
                }
                $azList[$currentPrefix]['sublist'][] = $item;
                uasort($azList[$currentPrefix]['sublist'], array($this,'sortByName'));
                ksort($azList);
            }
        }
        return $azList;
    }

    public function azPrefixListCollator($list, $property, $locale)
    {
        $collator = collator_create($locale);
        $collator->setAttribute(\Collator::QUATERNARY, \Collator::ON);
        $collator->setAttribute(\Collator::CASE_FIRST, \Collator::ON);
        $collator->setAttribute(\Collator::NUMERIC_COLLATION, \Collator::ON);

        if (is_array($list)) {
            uasort($list, function ($itemA, $itemB) use ($collator, $property) {
                return collator_compare($collator, $itemA[$property], $itemB[$property]);
            });
        } else {
            $list = $list->sortWithCollator($property, $locale);
        }

        $azList = array();

        foreach ($list as $item) {
            $currentPrefix = self::sortFirstChar($item[$property]);
            if (!array_key_exists($currentPrefix, $azList)) {
                $azList[$currentPrefix] = array(
                    'prefix' => $currentPrefix,
                    'sublist' => array(),
                );
            }
            $azList[$currentPrefix]['sublist'][] = $item;
        }
        return $azList;
    }

    public function isValueInArray($value, $params)
    {
        $paramsArr = explode(',', $params);
        if (in_array($value, $paramsArr)) {
            return true;
        }
        return false;
    }

    public static function remoteInclude($uri)
    {
        $prepend = '';
        $append = '';
        if (\App::DEBUG) {
            $prepend = "<!-- include($uri) -->\n";
            $append = "\n<!-- /include($uri) -->";
        }
        if (\App::ESI_ENABLED) {
            // Varnish does not support https
            $uri = preg_replace('#^(https?:)?//#', 'http://', $uri);
            if (\App::DEBUG) {
                $prepend = "<!-- replaced uri=$uri --> " . $prepend;
            }
            return $prepend . '<esi:include src="' . $uri . '" />' . $append;
        } else {
            $useragent = 'Client-' . (defined("\App::IDENTIFIER") ? constant("\App::IDENTIFIER") : 'ZMS');
            $options = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: de\r\n" .
                            "Cookie: zms=development\r\n" .
                            "user-agent: $useragent \r\n"
                )
              );
            $context = stream_context_create($options);
            return $prepend . file_get_contents($uri, false, $context) . $append;
        }
    }

    public function includeUrl($withUri = true)
    {
        if (null === \App::$includeUrl) {
            /** @var Request $request */
            $request = $this->container['request'];
            $uri = (string)$request->getBasePath();
            if ($withUri) {
                $uri = $request->getBaseUrl();
                $uri = preg_replace('#^https?://[^/]+#', '', $uri); //Do not force protocoll or host
            }
            return Helper::proxySanitizeUri($uri);
        } else {
            return \App::$includeUrl;
        }
    }

    public function baseUrl()
    {
        return $this->includeUrl(false);
    }

    public function getEsiFromPath($path, $locale = false)
    {
        $localePath = ($locale && 'de' != $locale) ? '/' .$locale : '';
        return \App::$esiBaseUrl . $localePath . \App::$$path;
    }

    public function getClientHost()
    {
        $request = $this->container['request'];
        $headerList = ['host', 'x-forwarded-host'];
        foreach ($headerList as $headername) {
            if ($request->hasHeader($headername)) {
                $hostname = $request->getHeaderLine($headername);
            }
        }
        return $hostname;
    }

    protected static function toSortableString($string)
    {
        $string = strtr($string, array(
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            '€' => 'E',
        ));
        return $string;
    }

    protected static function sortByName($left, $right)
    {
        return strcmp(
            self::toSortableString(strtolower($left['name'])),
            strtolower(self::toSortableString($right['name']))
        );
    }

    protected static function sortFirstChar($string)
    {
        $firstChar = mb_substr($string, 0, 1);
        $firstChar = mb_strtoupper($firstChar);
        $firstChar = strtr($firstChar, array('Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U'));
        return $firstChar;
    }

    public function dumpAppProfiler()
    {
        $output = '<h2>App Profiles</h2>'
            .' <p>For debugging: This log contains runtime information.
            <strong>DISABLE FOR PRODUCTION!</strong></p><ul>';
        foreach (Profiler::$profileList as $entry) {
            if ($entry instanceof Profiler) {
                $output .= "<li>$entry</li>";
            } else {
                $output .= \Tracy\Debugger::dump($entry, true);
            }
        }
        return $output .'</ul>';
    }

    public function kindOfPayment($code)
    {
        $result = '';
        if ($code == 0) {
            $result = 'eccash';
        } elseif ($code == 1) {
            $result = 'nocash';
        } elseif ($code == 2) {
            $result = 'ec';
        } elseif ($code == 3) {
            $result = 'cash';
        } elseif ($code == 4) {
            $result = 'subscribecash';
        }
        return $result;
    }
}
