<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "provider.json";

    #[\Override]
    public function getDefaults()
    {
        return [
            'id' => 0,
            'name' => '',
            'source' => 'dldb',
            'parent_id' => null
        ];
    }

    #[\Override]
    public function addData($mergeData)
    {
        $refString = '$ref';
        if (
            (is_array($mergeData) || $mergeData instanceof \ArrayAccess)
            && isset($mergeData[$refString])
            && (!isset($mergeData['id']) || !isset($mergeData['source']))
        ) {
            $providerRef = $mergeData[$refString];
            $providerId = preg_replace('#^.*/(\d+)/$#', '$1', $providerRef);
            $mergeData['id'] = $providerId;
            $mergeData['source'] = preg_replace('#^.*provider/([^/]+)/\d+/$#', '$1', $providerRef);
        }
        if (isset($mergeData[$refString])) {
            unset($mergeData[$refString]);
        }
        if (isset($mergeData['parent_id'])) {
            $this['parent_id'] = $mergeData['parent_id'];
        }
        return parent::addData($mergeData);
    }

    public function hasRequest($requestId)
    {
        return $this->getRequestList()->hasRequests($requestId);
    }

    public function getRequestList()
    {
        $requestList = new \BO\Zmsentities\Collection\RequestList();
        if (isset($this['data']['services'])) {
            foreach ($this['data']['services'] as $item) {
                $request = new Request([
                    'id' => $item['service'],
                    'source' => 'dldb',
                    'link' => isset($item['url']) ? $item['url'] : '',
                ]);
                $requestList->addEntity($request);
            }
        }
        return $requestList;
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }

    public function getName()
    {
        return $this->toProperty()->name->get();
    }

    public function getDisplayName()
    {
        return $this->toProperty()->display_name->get();
    }

    public function getContact()
    {
        $contact = $this->toProperty()->contact->get();
        return new Contact($contact);
    }

    public function getLink()
    {
        return $this->toProperty()->link->get();
    }

    public function getAdditionalData()
    {
        return $this->toProperty()->data->get();
    }

    public function getSlotTimeInMinutes()
    {
        $data = $this->getAdditionalData();
        if (! is_array($data)) {
            return null;
        }
        return $data['slotTimeInMinutes'] ?? null;
    }

    public function getParentId()
    {
        return $this->toProperty()->parent_id->get();
    }
}
