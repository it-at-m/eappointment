<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

class Base extends \ArrayObject
{

    /**
     * return an ID for this entity
     *
     */
    public function getId()
    {
        if (!array_key_exists('id', $this)) {
            return false;
        }
        return $this['id'];
    }

    /**
     * return a name for this entity
     *
     */
    public function getName()
    {
        return $this['name'];
    }

    /**
     * return a path for this entity
     *
     */
    public function getPath()
    {
        if (!array_key_exists('path', $this)) {
            return false;
        }
        return $this['path'];
    }

    public function getLocale()
    {
        if (!array_key_exists('locale', $this['meta'])) {
            return false;
        }
        return $this['meta']['locale'];
    }

    public function getLink()
    {
        if (!array_key_exists('link', $this)) {
            return false;
        }
        return $this['link'];
    }

    public function getType()
    {
        if (!array_key_exists('type', $this)) {
            return false;
        }
        return $this['type'];
    }

    protected static function subcount($countable)
    {
        if (is_array($countable) || $countable instanceof \Countable) {
            return count($countable);
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function offsetSet($index, $value)
    {
        if ('data_json' == $index) {
            $value = json_decode($value, true);
            $this->exchangeArray($value);
        } else {
            if (stripos($index, '_json')) {
                $value = json_decode($value, true);
                $index = str_replace('_json', '', $index);
            }
            if (stripos($index, '__')) {
                static::doubleUnterlineToArray($this, $index, $value);
                return true;
            }
            
            parent::offsetSet($index, $value);
        }
    }

    public static function doubleUnterlineToArray(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('__', $key);
    
        $numKeys = count($keys);
        while ($numKeys > 1) {
            $key = array_shift($keys);
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
    
        return $array;
    }
}
