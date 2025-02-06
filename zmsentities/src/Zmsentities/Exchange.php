<?php

namespace BO\Zmsentities;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(PublicMethod)
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

    public function setPeriod(\DateTimeInterface $firstDay, \DateTimeInterface $lastDay, $period = 'day')
    {
        $this->firstDay = (new Day())->setDateTime($firstDay);
        $this->lastDay = (new Day())->setDateTime($lastDay);
        $this->period = $period;
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
        if (!is_array($values) && !$values instanceof \Traversable) {
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
        if (isset($entity['firstDay'])) {
            unset($entity['firstDay']);
        }
        if (isset($entity['lastDay'])) {
            unset($entity['lastDay']);
        }
        if (isset($entity['period'])) {
            unset($entity['period']);
        }
        return $entity;
    }

    public function getPositionByName($name)
    {
        if (isset($this->dictionary)) {
            foreach ($this->dictionary as $entry) {
                if (isset($entry['variable']) && $entry['variable'] == $name) {
                    return $entry['position'];
                }
            }
        }
        return false;
    }

    public function withCalculatedTotals(array $keysToCalculate = ['count'], $dateName = 'name')
    {
        $entity = clone $this;
        $namePosition = $this->getPositionByName($dateName);
        if ($namePosition) {
            $totals = array_fill(0, count($entity->data[0]), 0);
            $totals[$namePosition] = 'totals';
            foreach ($keysToCalculate as $name) {
                $calculatePosition = $this->getPositionByName($name);
                foreach ($this->data as $item) {
                    foreach ($item as $position => $data) {
                        if (is_numeric($data) && $calculatePosition == $position) {
                            $totals[$position] += $data;
                        }
                    }
                }
            }
            $entity->addDataSet($totals);
        }
        return $entity;
    }

    public function withMaxByHour(array $keysToCalculate = ['count'])
    {
        $entity = clone $this;
        $maxima = [];
        foreach ($entity->data as $dateItems) {
            foreach ($dateItems as $hour => $hourItems) {
                foreach ($hourItems as $key => $value) {
                    if (is_numeric($value) && in_array($key, $keysToCalculate)) {
                        $maxima[$hour][$key] = (
                          isset($maxima[$hour][$key]) && $maxima[$hour][$key] > $value
                        ) ? $maxima[$hour][$key] : $value;
                    }
                }
            }
            $entity->data['max'] = $maxima;
        }
        return $entity;
    }

    public function withRequestsSum($keysToCalculate = ['requestscount'])
    {
        $entity = clone $this;
        $sum = [];
        foreach ($entity->data as $name => $entry) {
            $sum[$name] = 0;
            foreach ($entry as $dateItem) {
                foreach ($dateItem as $key => $value) {
                    if (is_numeric($value) && in_array($key, $keysToCalculate)) {
                        $sum[$name] += $value;
                    }
                }
            }
        }
        $entity->data['Summe'] = $sum;
        return $entity;
    }

    public function withAverage($keyToCalculate)
    {
        $entity = clone $this;
        $average = [];
    
        foreach ($entity->data as $name => $entry) {
            if (!is_array($entry) && !($entry instanceof \Traversable)) {
                // Skip or handle non-iterable $entry appropriately.
                continue;
            }
    
            $average[$name . '_sum'] = 0;
            $average[$name . '_count'] = 0;
    
            foreach ($entry as $dateItem) {
                if (!is_array($dateItem) && !($dateItem instanceof \Traversable)) {
                    // Skip or handle non-iterable $dateItem appropriately.
                    continue;
                }
    
                foreach ($dateItem as $key => $value) {
                    if (!is_numeric($value) || $key !== $keyToCalculate) {
                        // Skip non-numeric values or when the key doesn't match.
                        continue;
                    }
    
                    $average[$name . '_sum'] += $value;
                    $average[$name . '_count']++;
                }
            }
    
            $average[$name] = $average[$name . '_count'] > 0
                ? round($average[$name . '_sum'] / $average[$name . '_count'], 2)
                : null;
        }
    
        $entity->data['average_' . $keyToCalculate] = $average;
        return $entity;
    }
    

    public function withMaxAndAverageFromWaitingTime()
    {
        $entity = clone $this;
        foreach ($entity->data as $date => $dateItems) {
            $maxima = 0;
            $total = 0;
            $count = 0;
            foreach ($dateItems as $hourItems) {
                foreach ($hourItems as $key => $value) {
                    if (is_numeric($value) && 'waitingtime' == $key && 0 < $value) {
                        $total += $value;
                        $count += 1;
                        $maxima = ($maxima > $value) ? $maxima : $value;
                    }
                }
            }
            $entity->data[$date]['max'] = $maxima;
            $entity->data[$date]['average'] = (! $total || ! $count) ? 0 : floor($total / $count);
        }
        return $entity;
    }

    public function getCalculatedTotals()
    {
        foreach (array_reverse($this->data) as $item) {
            foreach ($item as $data) {
                if ($data == 'totals') {
                    return $item;
                }
            }
        }
        return null;
    }

    public function toHashed(array $hashfields = [])
    {
        $entity = clone $this;
        $entity->data = $this->getHashData($hashfields);
        unset($entity->dictionary);
        return $entity;
    }

    public function getHashData(array $hashfields = [], $first = false)
    {
        $hash = [];
        foreach ($this->dictionary as $entry) {
            foreach ($this->data as $key => $item) {
                foreach ($item as $position => $data) {
                    if ($entry['position'] == $position) {
                        if (count($hashfields) && in_array($entry['variable'], $hashfields)) {
                            $hash[$key][$entry['variable']] = $data;
                        } else {
                            $hash[$key][$entry['variable']] = $data;
                        }
                    }
                }
            }
        }
        return ($first) ? reset($hash) : $hash;
    }

    public function toGrouped(array $fields, array $hashfields)
    {
        $entity = clone $this;
        $entity->data = $this->getGroupedHashSet($fields, $hashfields);
        unset($entity->dictionary);
        return $entity;
    }

    public function getGroupedHashSet(array $fields, array $hashfields)
    {
        $list = [];
        if (count($fields)) {
            $field = array_shift($fields);
            $fieldposition = $this->getPositionByName($field);
            $requestscountPosition = $this->getPositionByName('requestscount');

            foreach ($this->data as $element) {
                if (isset($element[$fieldposition])) {
                    if (!isset($list[$element[$fieldposition]])) {
                        $list[$element[$fieldposition]] = clone $this;
                        $list[$element[$fieldposition]]->data = [];
                    }
                    if ($requestscountPosition !== false && isset($element[$requestscountPosition])) {
                        $element[$requestscountPosition] = (int) $element[$requestscountPosition];
                    }

                    $list[$element[$fieldposition]]->data[] = $element;
                }
            }

            foreach ($list as $key => $row) {
                if ($row instanceof Exchange) {
                    $list[$key] = $row->getGroupedHashSet($fields, $hashfields);
                }
            }
        } else {
            return $this->getHashData($hashfields, true);
        }
        return $list;
    }
}
