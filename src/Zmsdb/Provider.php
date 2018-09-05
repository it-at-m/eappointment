<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveReferences = 0)
    {
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionProviderSource($source)
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        $provider = $this->readResolvedReferences($provider, $resolveReferences);
        return $provider;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $provider, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $provider = $this->readWithRequestRelation($provider, $resolveReferences - 1);
        }
        return $provider;
    }

    public function readWithRequestRelation(\BO\Zmsentities\Schema\Entity $provider, $resolveReferences)
    {
        if ($provider->hasId()) {
            $requestRelationList = (new RequestRelation)->readListByProviderId($provider->getId(), $resolveReferences);
            $provider->requestrelation = $requestRelationList->toRequestRelation();
        }
        return $provider;
    }

    /**
     * @SuppressWarnings(Param)
     *
     */
    protected function readCollection($query, $resolveReferences)
    {
        $providerList = new Collection();
        $statement = $this->fetchStatement($query);
        while ($providerData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcessJoins($providerData));
            $entity = $this->readResolvedReferences($entity, $resolveReferences);
            $providerList->addEntity($entity);
        }
        return $providerList;
    }

    public function readList($source, $resolveReferences = 0, $isAssigned = null)
    {
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addConditionProviderSource($source)
            ->addEntityMapping();
        if (null !== $isAssigned) {
            $query->addConditionIsAssigned($isAssigned);
        }
        return $this->readCollection($query, $resolveReferences);
    }

    public function readListByRequest($source, $requestIdCsv, $resolveReferences = 0)
    {
        $query = new Query\Provider(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionProviderSource($source);
        $query->addConditionRequestCsv($requestIdCsv);
        return $this->readCollection($query, $resolveReferences);
    }

    public function readListBySource($source, $resolveReferences = 0)
    {
        $query = new Query\Provider(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionProviderSource($source);
        return $this->readCollection($query, $resolveReferences);
    }

    public function writeImportList($providerList, $source = 'dldb', $returnList = false)
    {
        foreach ($providerList as $provider) {
            $this->writeImportEntity($provider, $source);
        }
        if ($returnList) {
            return $this->readListBySource($source);
        }
    }

    public function writeImportEntity($provider, $source = 'dldb', $returnEntity = false)
    {
        if ($provider['address']['postal_code']) {
            $query = new Query\Provider(Query\Base::REPLACE);
            $query->addValues([
                'source' => $source,
                'id' => $provider['id'],
                'name' => $provider['name'],
                'contact__city' => $provider['address']['city'],
                'contact__country' => $provider['address']['city'],
                'contact__lat' => $provider['geo']['lat'],
                'contact__lon' => $provider['geo']['lon'],
                'contact__postalCode' => intval($provider['address']['postal_code']),
                'contact__region' => $provider['address']['city'],
                'contact__street' => $provider['address']['street'],
                'contact__streetNumber' => $provider['address']['house_number'],
                'link' => ('dldb' == $source)
                    ? 'https://service.berlin.de/standort/'. $provider['id'] .'/'
                    : ((isset($provider['link'])) ? $provider['link'] : ''),
                'data' => json_encode($provider)
            ]);
            $this->writeItem($query);
            if ($returnEntity) {
                return $this->readEntity($source, $provider['id']);
            }
        }
    }
}
