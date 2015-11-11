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
}
