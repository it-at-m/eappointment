<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static $schema = "provider.json";

    public function getDefaults()
    {
        return [
            'id' => 0,
            'name' => '',
            'source' => 'dldb',
            'parent_id' => null
        ];
    }

    public function addData($input)
    {
        $refString = '$ref';
        if (
            (is_array($input) || $input instanceof \ArrayAccess)
            && isset($input[$refString])
            && (!isset($input['id']) || !isset($input['source']))
        ) {
            $providerRef = $input[$refString];
            $providerId = preg_replace('#^.*/(\d+)/$#', '$1', $providerRef);
            $input['id'] = $providerId;
            $input['source'] = preg_replace('#^.*provider/([^/]+)/\d+/$#', '$1', $providerRef);
        }
        if (isset($input[$refString])) {
            unset($input[$refString]);
        }
        if (isset($input['parent_id'])) {
            $this['parent_id'] = $input['parent_id'];
        }
        return parent::addData($input);
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
        return $this->getAdditionalData()['slotTimeInMinutes'];
    }

    public function getParentId()
    {
        return $this->toProperty()->parent_id->get();
    }

    public function isDeletable(): bool
    {
        return (bool) ($this['canDelete'] ?? false);
    }
}
