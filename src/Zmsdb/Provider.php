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
