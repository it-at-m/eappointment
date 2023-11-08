<?php

namespace BO\Zmsentities\Schema\Extensions;

final class CoerceType implements \League\JsonGuard\ConstraintInterface
{
    const KEYWORD = 'type';

    private $typeConstraint;

    public function __construct()
    {
        $this->typeConstraint = new \League\JsonGuard\Constraint\DraftFour\Type();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, $typeList, \League\JsonGuard\Validator $validator)
    {
        $error = null;
        foreach ((array)$typeList as $type) {
            $value = $this->toCoercedType($value, $type);
            $error = $this->typeConstraint->validate($value, $type, $validator);
            if (is_null($error)) {
                return null;
            }
        }
        return $error;
    }

    private function toCoercedType($value, $type)
    {
        if ($type === 'number') {
            $value = $this->toNumber($value);
        } elseif ($type === 'string') {
            $value = $this->toString($value);
        } elseif ($type === 'integer' && !is_int($value)) {
            $value = $this->toNumber($value);
        } elseif ($type === 'boolean' && ($value !== true || $value !== false)) {
            $value = $this->toBoolean($value);
        }
        return $value;
    }

    private function toNumber($value)
    {
        if (is_int($value) || is_float($value)) {
            //do nothing
        } elseif ($value === true) {
            $value  = 1;
        } elseif ($value === false || $value === null) {
            $value = 0;
        } elseif (is_numeric($value)) {
            $value = (int)$value;
        }
        return $value;
    }

    private function toString($value)
    {
        if (is_string($value)) {
            //do nothing
        } elseif ($value === true) {
            $value  = "1";
        } elseif ($value === false || $value === null) {
            $value = "";
        }
        return $value;
    }

    private function toBoolean($value)
    {
        return $value ? true : false;
    }
}
