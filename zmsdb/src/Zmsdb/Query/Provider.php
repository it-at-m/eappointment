<?php

namespace BO\Zmsdb\Query;

class Provider extends Base
{
    const TABLE = 'provider';

    public function getEntityMapping()
    {
        $mapping = [
            'contact__city' => 'provider.contact__city',
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'provider.name',
            'contact__postalCode' => 'provider.contact__postalCode',
            'contact__region' => 'provider.contact__region',
            'contact__street' => 'provider.contact__street',
            'contact__streetNumber' => 'provider.contact__streetNumber',
            'id' => 'provider.id',
            'link' => 'provider.link',
            'name' => 'provider.name',
            'displayName' => 'provider.display_name',
            'source' => 'provider.source'
        ];
        if ($this->getResolveLevel() > 0) {
            $mapping['data'] = 'provider.data';
        }
        return $mapping;
    }

    public function addConditionIsAssigned($isAssigned)
    {
        $this->leftJoin(
            new Alias(Scope::TABLE, 'assignedscope'),
            'provider.id',
            '=',
            'assignedscope.InfoDienstleisterID'
        );
        if (true === $isAssigned) {
            $this->query->where('assignedscope.InfoDienstleisterID', 'IS NOT', null);
        } elseif (false === $isAssigned) {
            $this->query->where('assignedscope.InfoDienstleisterID', 'IS', null);
        }
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where('provider.id', '=', $providerId);
        return $this;
    }

    public function addConditionProviderSource($source)
    {
        $this->query->where('provider.source', '=', $source);
        return $this;
    }

    /**
     * @todo find calls and implement "sourceName"-parameter to remove default value
     */
    public function addConditionRequestCsv($requestIdCsv, $sourceName = 'dldb')
    {
        $requestIdList = explode(',', $requestIdCsv);
        $this->leftJoin(
            new Alias("request_provider", 'xprovider'),
            'provider.id',
            '=',
            'xprovider.provider__id'
        );
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($requestIdList, $sourceName) {
            $query->andWith('xprovider.request__id', 'IN', $requestIdList);
            $query->andWith('xprovider.bookable', '=', 1);
            $query->andWith('xprovider.source', '=', $sourceName);
        });
    }

    public function postProcess($data)
    {
        if (isset($data[$this->getPrefixed('data')]) && $data[$this->getPrefixed('data')]) {
            $data[$this->getPrefixed('data')] = json_decode($data[$this->getPrefixed('data')], true);
        }
        return $data;
    }
}
