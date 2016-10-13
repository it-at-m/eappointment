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
            ->addEntityMapping()
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        if ($resolveReferences > 0) {
            $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $providerId);
        }
        return $provider;
    }

    protected static function testSource($source)
    {
        if ('dldb' !== $source) {
            throw new Exception\UnknownDataSource("Unknown source ". htmlspecialchars($source));
        }
    }

    protected function readCollection($query, $source, $resolveReferences)
    {
        $providerList = new Collection();
        $statement = $this->fetchStatement($query);
        while ($providerData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $provider = new Entity($providerData);
            if ($resolveReferences > 0) {
                $provider['data'] = Helper\DldbData::readExtendedProviderData($source, $provider['id']);
            }
            $providerList->addEntity($provider);
        }
        return $providerList;
    }

    public function readList($source, $resolveReferences = 0, $isAssigned = null)
    {
        self::testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->addEntityMapping();
        if (null !== $isAssigned) {
            $query->addConditionIsAssigned($isAssigned);
        }
        return $this->readCollection($query, $source, $resolveReferences);
    }

    public function readListByRequest($source, $requestIdCsv, $resolveReferences = 0)
    {
        self::testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionRequestCsv($requestIdCsv);
        return $this->readCollection($query, $source, $resolveReferences);
    }
}
