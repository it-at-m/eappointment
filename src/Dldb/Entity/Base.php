<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

class Base extends \ArrayObject
{
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

    public function __set($name, $value) {
        $this->offsetSet($name, $value);
    }

    public function offsetSet($index, $value) {
        if ('data_json' == $index) {
            $value = json_decode($value, true);
            $this->exchangeArray($value);
        }
        else {
            if (stripos($index, '_json')) {
                $value = json_decode($value, true);
                $index = str_replace('_json', '', $index);
            }
            parent::offsetSet($index, $value);
        }
    }
}
