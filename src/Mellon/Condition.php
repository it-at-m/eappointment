<?php

namespace BO\Mellon;

/**
 *
 */
class Condition
{

    protected $collection = [];

    public function __construct(Valid ...$validList)
    {
        foreach ($validList as $valid) {
            $this->addValid($valid);
        }
    }

    public function addValid(Valid $valid)
    {
        $this->collection = $valid;
        return $this;
    }

    public function getCollection(): Collection
    {
        return new Collection($this->collection);
    }

    public function hasFailed(): bool
    {
        return $this->getCollection()->hasFailed();
    }

    public function __invoke()
    {
        return $this->hasFailed();
    }
}
