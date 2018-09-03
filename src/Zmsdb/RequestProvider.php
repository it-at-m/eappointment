<?php
namespace BO\Zmsdb;

class RequestProvider extends Base
{
    
    public function readSlotsOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = Query\RequestProvider::getQuerySlotsByRequestId();
        $providerSlots = $this->getReader()->fetchAll($query, ['request_id' => $entity->id]);
        return $providerSlots;
    }

    public function readSlotsOnEntityByProvider($requestId, $providerId)
    {
        $query = Query\RequestProvider::getQueryRequestSlotCount();
        $requestSlotCount = $this->getReader()->fetchValue($query, [
            'request_id' => $requestId,
            'provider_id' => $providerId
        ]);
        return $requestSlotCount;
    }

    public function writeList($providerList, $source = 'dldb')
    {
        foreach ($providerList as $provider) {
            if ($provider['address']['postal_code']) {
                foreach ($provider['services'] as $reference) {
                    $query = new Query\RequestProvider(Query\Base::REPLACE);
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
    }
}
