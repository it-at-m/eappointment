<?php

namespace BO\Zmsentities;

class Source extends Schema\Entity
{
    const PRIMARY = 'source';

    public static $schema = 'source.json';

    public function getDefaults()
    {
        return [
            'source' => '',
            'contact' => new Contact([
                'name' => '',
                'email' => ''
            ]),
            'providers' => new Collection\ProviderList(),
            'requests' => new Collection\RequestList(),
            'requestrelation' => new Collection\RequestRelationList(),
            'label' => '',
            'editable' => false
        ];
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }

    public function getLabel()
    {
        return $this->toProperty()->label->get();
    }

    public function getContact()
    {
        return $this->toProperty()->contact->get();
    }

    public function getProviderList()
    {
        $providerList = new Collection\ProviderList();
        foreach ($this->toProperty()->providers->get() as $provider) {
            if (! $provider instanceof Provider) {
                $provider = new Provider($provider);
            }
            $providerList->addEntity($provider);
        }
        return $providerList;
    }

    public function getRequestList()
    {
        $requestList = new Collection\RequestList();
        foreach ($this->toProperty()->requests->get() as $request) {
            if (! $request instanceof Request) {
                $request = new Request($request);
            }
            $requestList->addEntity($request);
        }
        return $requestList;
    }

    public function hasProvider($providerIdCsv)
    {
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (! in_array($providerId, $this->getProviderList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function getRequestRelationList()
    {
        $requestRelationList = new \BO\Zmsentities\Collection\RequestRelationList();
        if (isset($this['requestrelation'])) {
            foreach ($this['requestrelation'] as $entity) {
                if (! $entity instanceof RequestRelation) {
                    $entity = new RequestRelation($entity);
                }
                $requestRelationList->addEntity($entity);
            }
        }
        return $requestRelationList;
    }

    public function hasRequest($requestIdCsv)
    {
        $requestIds = explode(',', $requestIdCsv);
        foreach ($requestIds as $requestId) {
            if (! in_array($requestId, $this->getRequestList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function isEditable()
    {
        return ($this->toProperty()->editable->get()) ? true : false;
    }

    public function isCompleteAndEditable()
    {
        return ($this->isEditable() && 0 < $this->getProviderList()->count() && $this->getRequestList()->count());
    }

    public function withCleanedUpFormData()
    {
        $entity = parent::withCleanedUpFormData();
        $providerList = $entity->getProviderList();
        $requestList = $entity->getRequestList();
        $entity->providers = $providerList->withDataAsObject();
        $entity->requests = $requestList->withDataAsObject();
        return $entity;
    }
}
