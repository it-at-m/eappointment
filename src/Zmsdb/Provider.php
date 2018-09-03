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
            $requestRelationList = new \BO\Zmsentities\Collection\RequestRelationList();
            foreach ($this->readSlotCountById($provider->getId()) as $item) {
                $request = new \BO\Zmsentities\Request([
                    'id' => $item['request__id'],
                    '$ref' => '/request/'. $provider->source .'/'. $item['request__id'] .'/'
                ]);
                if (1 <= $resolveReferences) {
                    $request = (new Request)->readEntity($provider->source, $request->getId(), $resolveReferences - 1);
                }
                $entity = new \BO\Zmsentities\RequestRelation([
                    'request' => $request,
                    'slots' => $item['slots']
                ]);
                $requestRelationList->addEntity($entity);
            }
            $provider['requestrelation'] = $requestRelationList;
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
            $provider = new Entity($query->postProcessJoins($providerData));
            $providerList->addEntity($provider);
        }
        return $providerList;
    }

    public function readSlotCountById($providerId)
    {
        $query = Query\Provider::getQuerySlots();
        $slotCounts = $this->getReader()->fetchAll($query, ['provider_id' => $providerId]);
        return $slotCounts;
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
}
