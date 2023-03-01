<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Provider as Entity;
use \BO\Zmsentities\Collection\ProviderList as Collection;

class Provider extends Base
{
    public function readEntity($source, $providerId, $resolveReferences = 0)
    {
        $this->testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderSource($source)
            ->addConditionProviderId($providerId);
        $provider = $this->fetchOne($query, new Entity());
        return $provider;
    }

    /**
     * @SuppressWarnings(Param)
     *
     */
    protected function readCollection($query)
    {
        $providerList = new Collection();
        $statement = $this->fetchStatement($query);
        while ($providerData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entity = new Entity($query->postProcessJoins($providerData));
            $providerList->addEntity($entity);
        }
        return $providerList;
    }

    public function readListBySource($source, $resolveReferences = 0, $isAssigned = null, $requestIdCsv = null)
    {
        $this->testSource($source);
        $query = new Query\Provider(Query\Base::SELECT);
        $query->setDistinctSelect();
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionProviderSource($source);
        if (null !== $isAssigned) {
            $query->addConditionIsAssigned($isAssigned);
        }
        if (null !== $requestIdCsv) {
            $query->addConditionRequestCsv($requestIdCsv, $source);
        }
        $providerList = $this->readCollection($query);
        return ($providerList->count()) ? $providerList->sortById() : $providerList;
    }

    public function writeEntity(Entity $entity)
    {
        $this->writeDeleteEntity($entity->getId(), $entity->getSource());
        $contact =  $entity->getContact();
        if (! $contact->count()) {
            throw new Exception\Provider\ProviderContactMissed();
        }
        $query = new Query\Provider(Query\Base::INSERT);
        $additionalData = $entity->getAdditionalData() ?? [];
        
        $query->addValues([
            'source' => $entity->getSource(),
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'display_name' => $additionalData && isset($additionalData['displayName'])
                ? $additionalData['displayName']
                : $entity->getName(),
            'contact__city' => $contact->getProperty('city'),
            'contact__country' => $contact->getProperty('country'),
            'contact__lat' => $contact->getProperty('lat', 0),
            'contact__lon' => $contact->getProperty('lon', 0),
            'contact__postalCode' => intval($contact->getProperty('postalCode')),
            'contact__region' => $contact->getProperty('region'),
            'contact__street' => $contact->getProperty('street'),
            'contact__streetNumber' => $contact->getProperty('streetNumber', '-'),
            'link' =>  ($entity->getLink()) ? $entity->getLink() : '',
            'data' => ($entity->getAdditionalData()) ? json_encode($entity->getAdditionalData()) : '{}'
        ]);
        $this->writeItem($query);
        return $this->readEntity($entity->getSource(), $entity->getId());
    }

    public function writeListBySource(\BO\Zmsentities\Source $source)
    {
        $this->writeDeleteListBySource($source->getSource());
        foreach ($source->getProviderList() as $provider) {
            $this->writeEntity($provider);
        }
        return $this->readListBySource($source->getSource());
    }

    public function writeImportList($providerList, $source = 'dldb')
    {
        foreach ($providerList as $provider) {
            $this->writeImportEntity($provider, $source);
        }
        return $this->readListBySource($source, 1);
    }

    public function writeImportEntity($provider, $source = 'dldb')
    {
        if ($provider['address']['postal_code']) {
            $query = new Query\Provider(Query\Base::REPLACE);
            $query->addValues([
                'source' => $source,
                'id' => $provider['id'],
                'name' => $provider['name'],
                'display_name' => $provider['displayName'] ?? $provider['name'],
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
            $provider = $this->readEntity($source, $provider['id']);
        }
        return $provider;
    }

    public function writeDeleteEntity($providerId, $source)
    {
        $query = new Query\Provider(Query\Base::DELETE);
        $query->addConditionProviderId($providerId);
        $query->addConditionProviderSource($source);
        return $this->deleteItem($query);
    }

    public function writeDeleteListBySource($source)
    {
        $query = new Query\Provider(Query\Base::DELETE);
        $query->addConditionProviderSource($source);
        return $this->deleteItem($query);
    }

    protected function testSource($source)
    {
        if (! (new Source())->readEntity($source)) {
            throw new Exception\Source\UnknownDataSource();
        }
    }
}
