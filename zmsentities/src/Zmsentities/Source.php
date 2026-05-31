<?php

namespace BO\Zmsentities;

class Source extends Schema\Entity
{
    public const PRIMARY = 'source';

    public static ?string $schema = 'source.json';

    /**
     * @return (Collection\ProviderList|Collection\RequestList|Collection\RequestRelationList|Contact|false|string)[]
     *
     * @psalm-return array{source: '', contact: Contact, providers: Collection\ProviderList, requests: Collection\RequestList, requestrelation: Collection\RequestRelationList, label: '', editable: false}
     */
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

    public function getProviderList(): Collection\ProviderList
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

    public function getScopeList(): Collection\ScopeList
    {
        $scopeList = new Collection\ScopeList();
        $scopes = $this->toProperty()->scopes->get();
        if ($scopes && is_iterable($scopes)) {
            foreach ($scopes as $scope) {
                if (!$scope instanceof Scope) {
                    $scope = new Scope($scope);
                }
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    public function getRequestList(): Collection\RequestList
    {
        $requestList = new Collection\RequestList();
        $requests = $this->toProperty()->requests->get();
        if ($requests && is_iterable($requests)) {
            foreach ($requests as $request) {
                if (! $request instanceof Request) {
                    $request = new Request($request);
                }
                $requestList->addEntity($request);
            }
        }
        return $requestList;
    }

    public function hasProvider($providerIdCsv): bool
    {
        $providerIds = explode(',', $providerIdCsv);
        foreach ($providerIds as $providerId) {
            if (! in_array($providerId, $this->getProviderList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function getRequestRelationList(): Collection\RequestRelationList
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

    public function hasRequest($requestIdCsv): bool
    {
        $requestIds = explode(',', $requestIdCsv);
        foreach ($requestIds as $requestId) {
            if (! in_array($requestId, $this->getRequestList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function isEditable(): bool
    {
        return ($this->toProperty()->editable->get()) ? true : false;
    }

    public function isCompleteAndEditable(): bool
    {
        $source = $this->getSource();
        if (empty($source) || !is_string($source)) {
            return false;
        }
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
