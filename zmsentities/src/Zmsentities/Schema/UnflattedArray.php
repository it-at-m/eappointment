<?php

namespace BO\Zmsentities\Schema;

class UnflattedArray
{
    protected $value = null;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
      * split fields
      * If a key to a field has two underscores "__" it should go into a subarray
      * ATTENTION: performance critical function, keep highly optimized!
      * @param  array $hash
      *
      * @return array
      */
    public function getUnflattenedArray()
    {
        $hash = $this->value;
        foreach ($hash as $key => $value) {
            if (false !== strpos($key, '__')) {
                $currentLevel =& $hash;
                unset($hash[$key]);
                foreach (explode('__', $key) as $currentKey) {
                    if (!isset($currentLevel[$currentKey])) {
                        $currentLevel[$currentKey] = [];
                    }
                    $currentLevel =& $currentLevel[$currentKey];
                }
                $currentLevel = $value;
            }
        }
        $this->value = $hash;
        return (array)$hash;
    }
}
