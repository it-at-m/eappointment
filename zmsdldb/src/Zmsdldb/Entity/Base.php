<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Entity;

/**
 * @extends \ArrayObject<string, mixed>
 */
class Base extends \ArrayObject
{
    /**
     * return an ID for this entity
     *
     */
    public function getId(): mixed
    {
        if (!$this->offsetExists('id')) {
            return false;
        }
        return $this['id'];
    }

    /**
     * return a name for this entity
     *
     */
    public function getName(): mixed
    {
        return $this['name'];
    }

    /**
     * return a path for this entity
     *
     * @psalm-api
     */
    public function getPath(): mixed
    {
        if (!$this->offsetExists('path')) {
            return false;
        }
        return $this['path'];
    }

    public static function hasValidOffset(mixed $item, string $index): bool
    {
        return (
            (is_object($item) && $item->offsetExists($index)) ||
            (is_array($item) && array_key_exists($index, $item))
        );
    }

    /** @psalm-api */
    public function getLocale(): mixed
    {
        $meta = $this['meta'];
        if (false === static::hasValidOffset($meta, 'locale')) {
            return false;
        }
        return $this['meta']['locale'];
    }

    /** @psalm-api */
    public function getLink(): mixed
    {
        if (!$this->offsetExists('link')) {
            return false;
        }
        return $this['link'];
    }

    public function getType(): mixed
    {
        if (!$this->offsetExists('type')) {
            return false;
        }
        return $this['type'];
    }

    protected static function subcount(mixed $countable): int|null
    {
        if (is_array($countable) || $countable instanceof \Countable) {
            return count($countable);
        }
        return null;
    }

    public function __set(string $name, mixed $value)
    {
        $this->offsetSet($name, $value);
    }

    #[\Override]
    public function offsetSet($index, $value): void
    {
        if ('data_json' == $index) {
            $value = json_decode($value, true);
            $this->exchangeArray($value);
        } else {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (stripos($index, '_json')) {
                $value = json_decode($value, true);
                $index = str_replace('_json', '', $index);
            }
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (stripos($index, '__')) {
                static::doubleUnterlineToArray($this, $index, $value);
                return;
            }

            parent::offsetSet($index, $value);
        }
    }

    /**
     * @param static $array
     */
    public static function doubleUnterlineToArray(&$array, string $key, mixed $value): mixed
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('__', $key);

        $numKeys = count($keys);
        while ($numKeys > 1) {
            $key = array_shift($keys);
            $numKeys = count($keys);
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}
