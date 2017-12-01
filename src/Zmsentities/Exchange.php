<?php

namespace BO\Zmsentities;

/**
 *
 */
class Exchange extends Schema\Entity
{
    const PRIMARY = 'firstDay';

    public static $schema = "exchange.json";

    public function getDefaults()
    {
        return [
            'firstDay' => new Day(),
            'lastDay' => new Day(),
            'period' => 'day',
            'dictionary' => [ ],
            'data' => [ ]
        ];
    }

    public function setPeriod(\DateTimeInterface $firstDay, \DateTimeInterface $lastDay)
    {
        $this->firstDay = (new Day())->setDateTime($firstDay);
        $this->lastDay = (new Day())->setDateTime($lastDay);
        return $this;
    }

    public function addDictionaryEntry($variable, $type = 'string', $description = '', $reference = '')
    {
        $position = count($this['dictionary']);
        $this['dictionary'][$position] = [
            'position' => $position,
            'variable' => $variable,
            'type' => $type,
            'description' => $description,
            'reference' => $reference
        ];
        return $this;
    }

    public function addDataSet($values)
    {
        if (!is_array($values) && !$values instanceof Traversable) {
            throw new \Exception("Values have to be of type array");
        }
        if (count($this->dictionary) != count($values)) {
            throw new \Exception("Mismatching dictionary settings for values (count mismatch)");
        }
        $this->data[] = $values;
    }

    public function withLessData()
    {
        $entity = clone $this;
        unset($entity['firstDay']);
        unset($entity['lastDay']);
        unset($entity['period']);
        return $entity;
    }

    public function getReferenceByString($string = '')
    {
        $reference = [];
        if ($string) {
            $array = explode('.', $string);
            $reference['entity'] = reset($array);
            $reference['property'] = end($array);
        }
        return $reference;
    }
}
