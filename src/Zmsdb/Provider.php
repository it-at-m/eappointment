<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveReferences = 0)
    {
        self::testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        return $provider;
    }

    protected static function testSource($source)
    {
        if ('dldb' !== $source) {
            throw new Exception\UnknownDataSource("Unknown source ". htmlspecialchars($source));
        }
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
            $provider = new Entity($query->postProcess($providerData));
            $providerList->addEntity($provider);
        }
        return $providerList;
    }

    public function readSlotCountById($providerId)
    {
        $query = Query\Provider::getQuerySlots();
        $slotCounts = $this->getReader()->fetchAll(
            $query,
            ['provider_id' => $providerId]
        );
        return $slotCounts;
    }

    public function readList($source, $resolveReferences = 0, $isAssigned = null)
    {
        self::testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping();
        if (null !== $isAssigned) {
            $query->addConditionIsAssigned($isAssigned);
        }
        return $this->readCollection($query, $resolveReferences);
    }

    public function readListByRequest($source, $requestIdCsv, $resolveReferences = 0)
    {
        self::testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionRequestCsv($requestIdCsv);
        return $this->readCollection($query, $resolveReferences);
    }
}
