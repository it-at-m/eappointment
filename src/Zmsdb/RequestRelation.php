<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\RequestRelation as Entity;

use \BO\Zmsentities\Collection\RequestRelationList as Collection;

class RequestRelation extends Base
{
    public function readEntity($requestId, $providerId, $resolveReferences = 0)
    {
        $query = new Query\RequestRelation(Query\Base::SELECT);
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
        $query = new Query\RequestRelation(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionSource($source);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement);
    }

    public function readListByRequestId($requestId, $resolveReferences = 0)
    {
        $query = new Query\RequestRelation(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestId($requestId);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement);
    }

    public function readListByProviderId($providerId, $resolveReferences = 0)
    {
        $query = new Query\RequestRelation(Query\Base::SELECT);
        $query
            ->setResolveLevel(0)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        $statement = $this->fetchStatement($query);
        return $this->readList($statement);
    }

    public function writeListBySource(\BO\Zmsentities\Source $source)
    {
        if ($source->getRequestRelationList()->count()) {
            foreach ($source->getRequestRelationList() as $entity) {
                $this->writeEntity($entity);
            }
        } else if ($source->isCompleteAndEditable()) {
            foreach($source->getProviderList() as $provider) {
                foreach($source->getRequestList() as $request) {
                    $entity = new Entity([
                        'source' => $source->getSource(),
                        'provider' => $provider,
                        'request' => $request,
                        'slots' => 1
                    ]);
                    $this->writeEntity($entity);
                }
            }
        }

        return $this->readListBySource($source->getSource());
    }

    public function writeEntity(Entity $entity)
    {
        $query = new Query\RequestRelation(Query\Base::REPLACE);
        $query->addValues([
            'source' => $entity->getSource(),
            'provider__id' => $entity->getProvider()->getId(),
            'request__id' => $entity->getRequest()->getId(),
            'slots' => $entity->getSlotCount()
        ]);
        $this->writeItem($query);
        return $this->readEntity($entity->getRequest()->getId(), $entity->getProvider()->getId());
    }

    public function writeImportList($providerList, $source = 'dldb')
    {
        foreach ($providerList as $provider) {
            if ($provider['address']['postal_code']) {
                foreach ($provider['services'] as $reference) {
                    $query = new Query\RequestRelation(Query\Base::REPLACE);
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
        return $this->readListBySource($source);
    }

    protected function readList($statement)
    {
        $collection = new Collection();
        while ($requestRelationData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($requestRelationData);
            $collection->addEntity($entity);
        }
        return $collection;
    }

    public function writeDeleteListBySource($source)
    {
        $query = new Query\RequestRelation(Query\Base::DELETE);
        $query->addConditionSource($source);
        return $this->deleteItem($query);
    }
}
