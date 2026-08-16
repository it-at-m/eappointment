<?php

/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
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
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** @psalm-api Called by Twig when the extension is registered. */
    public function getName(): string
    {
        return 'boslimExtension';
    }

    #[\Override]
    public function getFunctions(): array
    {
        $safe = array('is_safe' => array('html'));
        return array(
            new \Twig\TwigFunction('urlGet', array($this, 'urlGet')),
            new \Twig\TwigFunction('csvProperty', array($this, 'csvProperty')),
            new \Twig\TwigFunction('azPrefixList', array($this, 'azPrefixList')),
            new \Twig\TwigFunction('azPrefixListCollator', array($this, 'azPrefixListCollator')),
            new \Twig\TwigFunction('isValueInArray', array($this, 'isValueInArray')),
            new \Twig\TwigFunction('remoteInclude', array($this, 'remoteInclude'), $safe),
            new \Twig\TwigFunction('getEsiFromPath', array($this, 'getEsiFromPath')),
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


    public static function isImageAllowed(): bool
    {
        return (isset(\App::$isImageAllowed)) ? \App::$isImageAllowed : true;
    }

    public function getLanguageDescriptor(string $locale = 'de'): ?string
    {
        $language = \App::$supportedLanguages[$locale] ?? [];
        return $language['name'] ?? null;
    }

    public static function isNumeric(mixed $var): bool
    {
        return is_numeric($var);
    }

    public static function getNow(): \DateTimeInterface
    {
        if (\App::$now instanceof \DateTimeInterface) {
            return \App::$now;
        }
        return new \DateTimeImmutable();
    }

    /**
     * @return false|string
     */
    public static function getSystemStatus(string $env): string|false
    {
        return getenv($env);
    }

    public function toTextFormat(string $string): string
    {
        $string = \strip_tags($string, '<br />');
        $temp = str_replace(array("<br />"), "\n", $string);
        $lines = explode("\n", $temp);
        $new_lines = array();
        foreach ($lines as $line) {
            if (!empty($line)) {
                $new_lines[] = trim($line);
            }
        }
        $result = implode("\n", $new_lines);
        return addSlashes($result);
    }

    /**
     * @return array<string, mixed>
     */
    public function formatDateTime(object $dateString): array
    {
        $dateTime = new \DateTimeImmutable(
            $dateString->year . '-' . $dateString->month . '-' . $dateString->day,
            new \DateTimeZone('Europe/Berlin')
        );
        $formatDate = [];
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

    /**
     * @return array{name: mixed, params: array<array-key, mixed>}
     */
    public function currentRoute(?string $lang = null): array
    {
        $route = array(
            'name' => 'noroute',
            'params' => []
        );
        if ($this->container->has('currentRoute')) {
            $routeParams = $this->container->get('currentRouteParams');
            if (!is_array($routeParams)) {
                $routeParams = [];
            }
            if (null !== $lang && 'de' == $lang) {
                unset($routeParams['lang']);
            } elseif (self::isMultiLanguage()) {
                $routeParams['lang'] = ($lang !== null) ? $lang : (
                    \App::$language instanceof Language ? \App::$language->getCurrentLanguage() : 'de'
                );
            }

            $routeName = $this->container->get('currentRoute');
            $route = array(
                'name' => $routeName,
                'params' => $routeParams
            );
        }
        return $route;
    }

    public function currentLang(): string
    {
        if (self::isMultiLanguage() && \App::$language instanceof Language) {
            return \App::$language->getCurrentLanguage();
        }
        return 'de';
    }

    public function currentLocale(): string
    {
        $locale = 'de_DE';
        if (self::isMultiLanguage() && \App::$language instanceof Language) {
            $parts = explode('.', \App::$language->getCurrentLocale());
            $locale = $parts[0] !== '' ? $parts[0] : 'de_DE';
        }
        return $locale;
    }

    public function currentVersion(): array|string|null
    {
        $version = Version::getString();
        return ($version != Version::UNKNOWN) ? $version : Git::readCurrentVersion();
    }

    public function urlGet(string $routeName, array $params = [], array $getparams = []): string
    {
        $url = \App::$slim->urlFor($routeName, $params);
        $url = preg_replace('#^.*?(https?://)#', '\1', $url) ?? $url; // allow http:// routes
        if ($getparams !== []) {
            $url .= '?' . http_build_query($getparams);
        }
        $sanitized = Helper::proxySanitizeUri($url);
        return is_string($sanitized) ? $sanitized : $url;
    }

    public function csvProperty(iterable $list, string $property): string
    {
        $propertylist = array();
        foreach ($list as $item) {
            if (is_array($item) && array_key_exists($property, $item)) {
                $propertylist[] = $item[$property];
            }
        }
        return implode(',', array_unique($propertylist));
    }

    /**
     * @return array<string, array{prefix: string, sublist: list<mixed>}>
     */
    public function azPrefixList(iterable $list, string $property): array
    {
        $azList = array();
        foreach ($list as $item) {
            if (is_array($item) && array_key_exists($property, $item)) {
                $currentPrefix = self::sortFirstChar((string) $item[$property]);
                if (!array_key_exists($currentPrefix, $azList)) {
                    $azList[$currentPrefix] = array(
                        'prefix' => $currentPrefix,
                        'sublist' => array(),
                    );
                }
                $azList[$currentPrefix]['sublist'][] = $item;
                uasort(
                    $azList[$currentPrefix]['sublist'],
                    static fn ($left, $right) => self::sortByName($left, $right)
                );
                ksort($azList);
            }
        }
        return $azList;
    }

    /**
     * @return array<string, array{prefix: string, sublist: list<mixed>}>
     */
    public function azPrefixListCollator(mixed $list, string $property, string $locale): array
    {
        $collator = collator_create($locale);
        if ($collator === null) {
            return [];
        }
        $collator->setAttribute(\Collator::QUATERNARY, \Collator::ON);
        $collator->setAttribute(\Collator::CASE_FIRST, \Collator::ON);
        $collator->setAttribute(\Collator::NUMERIC_COLLATION, \Collator::ON);

        if (is_array($list)) {
            uasort($list, function ($itemA, $itemB) use ($collator, $property) {
                $compared = collator_compare($collator, $itemA[$property], $itemB[$property]);
                return (int) $compared;
            });
        } elseif (is_object($list) && method_exists($list, 'sortWithCollator')) {
            $list = $list->sortWithCollator($property, $locale);
        }

        $azList = array();

        foreach ($list as $item) {
            if (!is_array($item) || !array_key_exists($property, $item)) {
                continue;
            }
            $currentPrefix = self::sortFirstChar((string) $item[$property]);
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

    public function isValueInArray(mixed $value, string $params): bool
    {
        $paramsArr = explode(',', $params);
        if (in_array($value, $paramsArr)) {
            return true;
        }
        return false;
    }

    public static function remoteInclude(string $uri): string
    {
        $prepend = '';
        $append = '';
        if (self::isDebug()) {
            $prepend = "<!-- include($uri) -->\n";
            $append = "\n<!-- /include($uri) -->";
        }
        if (self::isEsiEnabled()) {
            // Varnish does not support https
            $httpUri = preg_replace('#^(https?:)?//#', 'http://', $uri);
            $uri = is_string($httpUri) ? $httpUri : $uri;
            if (self::isDebug()) {
                $prepend = "<!-- replaced uri=$uri --> " . $prepend;
            }
            return $prepend . '<esi:include src="' . $uri . '" />' . $append;
        }
        $useragent = 'Client-' . (defined("\App::IDENTIFIER") ? constant("\App::IDENTIFIER") : 'ZMS');
        $options = array(
            'http' => array(
              'method' => "GET",
              'header' => "Accept-language: de\r\n" .
                        "Cookie: zms=development\r\n" .
                        "user-agent: $useragent \r\n"
            )
          );
        $context = stream_context_create($options);
        $contents = file_get_contents($uri, false, $context);
        return $prepend . ($contents !== false ? $contents : '') . $append;
    }

    public function getEsiFromPath(string $path, string|false $locale = false): string
    {
        $localePath = ($locale !== false && 'de' != $locale) ? '/' . $locale : '';
        $base = \App::$esiBaseUrl ?? '';
        $pathValue = \App::$$path ?? '';
        return $base . $localePath . (is_string($pathValue) ? $pathValue : '');
    }

    public function getClientHost(): string
    {
        if (!$this->container->has('request')) {
            return '';
        }
        $request = $this->container->get('request');
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }
        $hostname = '';
        $headerList = ['host', 'x-forwarded-host'];
        foreach ($headerList as $headername) {
            if ($request->hasHeader($headername)) {
                $hostname = $request->getHeaderLine($headername);
            }
        }
        return $hostname;
    }

    protected static function toSortableString(string $string): string
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

    protected static function sortByName(mixed $left, mixed $right): int
    {
        $leftName = is_array($left) && isset($left['name']) ? (string) $left['name'] : '';
        $rightName = is_array($right) && isset($right['name']) ? (string) $right['name'] : '';
        return strcmp(
            self::toSortableString(strtolower($leftName)),
            strtolower(self::toSortableString($rightName))
        );
    }

    protected static function sortFirstChar(string $string): string
    {
        $firstChar = mb_substr($string, 0, 1);
        $firstChar = mb_strtoupper($firstChar);
        $firstChar = strtr($firstChar, array('Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U'));
        return $firstChar;
    }

    public function dumpAppProfiler(): string
    {
        $output = '<h2>App Profiles</h2>'
            . ' <p>For debugging: This log contains runtime information.
            <strong>DISABLE FOR PRODUCTION!</strong></p><ul>';
        foreach (Profiler::$profileList as $entry) {
            $output .= "<li>$entry</li>";
        }
        return $output . '</ul>';
    }

    public function kindOfPayment(mixed $code): string
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

    private static function isMultiLanguage(): bool
    {
        /** @psalm-suppress RedundantCondition Module App subclasses may set MULTILANGUAGE to false. */
        return \App::MULTILANGUAGE;
    }

    private static function isDebug(): bool
    {
        /** @psalm-suppress RedundantCondition */
        /** @psalm-suppress TypeDoesNotContainType Module App subclasses may set DEBUG to true. */
        return \App::DEBUG;
    }

    private static function isEsiEnabled(): bool
    {
        /** @psalm-suppress RedundantCondition Module App subclasses may set ESI_ENABLED to false. */
        return \App::ESI_ENABLED;
    }
}
