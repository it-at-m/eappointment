<?php

namespace BO\Zmsclient\GraphQL;

class GraphQLNode extends GraphQLElement
{
    public $propertyList = [];

    public $parent;

    public function addElement($propertyName): self
    {
        $this->propertyList[] = new GraphQLElement($propertyName);
        return $this;
    }

    protected function getLastKey()
    {
        $keys = array_keys($this->propertyList);
        return end($keys);
    }

    public function getFirstElement(): GraphQLElement
    {
        $first = reset($this->propertyList);
        $first = (!$first) ? new GraphQLNode('first') : $first;
        return $first;
    }

    public function getLastElement(): GraphQLElement
    {
        $lastkey = $this->getLastKey();
        $element = $this->propertyList[$lastkey];
        return $element;
    }

    public function addSubNode(): self
    {
        $lastkey = $this->getLastKey();
        if (false === $lastkey) {
            // root node
            $node = new self();
        } else {
            $node = new self($this->getLastElement()->propertyName);
        }
        $node->setParent($this);
        $this->propertyList[$lastkey] = $node;
        return $node;
    }

    public function setParent(self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function hasParent(): bool
    {
        return ($this->parent) ? true : false;
    }

    public function getParent(): self
    {
        if (!$this->hasParent()) {
            throw new GraphQLException("Curly bracket match problem, too many closing brackets");
        }
        return $this->parent;
    }

    public function getRealRoot(): self
    {
        if ($this->getFirstElement()->propertyName == '__root') {
            return $this->getFirstElement()->getRealRoot();
        }
        return $this;
    }

    public function getNodesFromIterable($data): array
    {
        $reduced = [];
        if (is_array($data) && array_values($data) === $data) {
            foreach ($data as $item) {
                $reduced[] = $this->getNodesFromIterable($item);
            }
        } else {
            foreach ($this->propertyList as $element) {
                $propertyName = $element->propertyName;
                if (isset($data[$propertyName])) {
                    if ($element instanceof self) {
                        $reduced[$propertyName] = $element->getNodesFromIterable($data[$propertyName]);
                    } else {
                        $reduced[$propertyName] = $data[$propertyName];
                    }
                }
            }
        }
        return $reduced;
    }
}
