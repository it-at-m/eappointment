<?php

namespace BO\Zmsdb\Query;

use DateTime;
use DateTimeInterface;

class Closure extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'closures';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getEntityMapping()
    {
        return [
            'id' => 'closure.id',
            'scopeId'    => 'closure.StandortID',
            'year' => 'closure.year',
            'month' => 'closure.month',
            'day' => 'closure.day',
            'lastChange' => 'closure.updateTimestamp'
        ];
    }

    public function addConditionDate(DateTime $date)
    {
        $this->query->where('closure.year', '=', $date->format('Y'));
        $this->query->where('closure.month', '=', $date->format('m'));
        $this->query->where('closure.day', '=', $date->format('d'));
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('closure.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionScopeIds(array $scopeIds)
    {
        $ids = array_values(array_unique(array_map('intval', $scopeIds)));

        if (empty($ids)) {
            $this->query->where(self::expression('1 = 0'));
            return $this;
        }

        if (count($ids) === 1) {
            return $this->addConditionScopeId($ids[0]);
        }

        $this->query->where(function ($conditions) use ($ids) {
            $first = true;
            foreach ($ids as $id) {
                if ($first) {
                    $conditions->andWith('closure.StandortID', '=', $id);
                    $first = false;
                } else {
                    $conditions->orWith('closure.StandortID', '=', $id);
                }
            }
        });

        return $this;
    }

    public function addConditionDateRange(\DateTimeInterface $from, \DateTimeInterface $until)
    {
        $dateExpr = self::expression(
            "DATE(CONCAT(closure.year,'-',LPAD(closure.month,2,'0'),'-',LPAD(closure.day,2,'0')))"
        );
        $this->query->where($dateExpr, '>=', $from->format('Y-m-d'));
        $this->query->where($dateExpr, '<=', $until->format('Y-m-d'));
        return $this;
    }

    public function addSelectVirtualDate()
    {
        $this->query->select([
            $this->getPrefixed('date') => self::expression(
                "DATE(CONCAT(closure.year,'-',LPAD(closure.month,2,'0'),'-',LPAD(closure.day,2,'0')))"
            )
        ]);
        return $this;
    }

    public function addConditionId($id)
    {
        $this->query->where('closure.id', '=', $id);
        return $this;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
