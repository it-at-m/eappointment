<?php

namespace BO\Mellon;

/**
 *
 */
class Condition
{
    /**
     * @var Collection|array
     */
    protected array|Collection $collection = [];

    public function __construct(Valid ...$validList)
    {
        $this->collection = new Collection([]);
        foreach ($validList as $valid) {
            $this->addValid($valid);
        }
    }

    public function addValid(Valid $valid): static
    {
        $this->getCollection()->addValid($valid);
        return $this;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
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
