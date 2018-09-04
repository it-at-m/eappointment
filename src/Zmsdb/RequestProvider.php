<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\RequestProvider as Entity;

use \BO\Zmsentities\Collection\RequestProviderList as Collection;

class RequestProvider extends Base
{
    public function readEntity($requestId, $providerId, $resolveReferences = 0)
    {
        $query = new Query\RequestProvider(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId)
            ->addConditionRequestId($requestId);
        return $this->fetchOne($query, new Entity());
    }

    public function readListBySource($source, $resolveReferences = 0)
    {
        $collection = new Collection();
        $query = new Query\RequestProvider(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionSource($source);
        foreach ($this->fetchList($query, new Entity()) as $entity) {
            $collection->addEntity($entity);
        }
        return $collection;
    }

    public function readListByRequestId($requestId, $resolveReferences = 0)
    {
        $query = new Query\RequestProvider(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestId($requestId);
        return $this->fetchList($query, new Entity());
    }

    public function readListByProviderId($providerId, $resolveReferences = 0)
    {
        $query = new Query\RequestProvider(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        return $this->fetchList($query, new Entity());
    }

    public function writeImportList($providerList, $source = 'dldb', $returnList = false)
    {
        foreach ($providerList as $provider) {
            if ($provider['address']['postal_code']) {
                foreach ($provider['services'] as $reference) {
                    $query = new Query\RequestProvider(Query\Base::REPLACE);
                    $query->addValues([
                        'source' => $source,
                        'provider__id' => $provider['id'],
                        'request__id' => $reference['service'],
                        'slots' => $reference['appointment']['slots']
                    ]);
                    $this->writeItem($query);
                }
            }
        }
        if ($returnList) {
            return $this->readListBySource($source);
        }
    }
}
