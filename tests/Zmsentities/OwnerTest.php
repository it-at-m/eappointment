<?php

namespace BO\Zmsentities\Tests;

class OwnerTest extends EntityCommonTests
{
    public $entityclass = '\BO\Zmsentities\Owner';

    public $collectionclass = '\BO\Zmsentities\Collection\OwnerList';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->hasOrganisation(456), 'organisation with id 456 not found');
    }

    public function testCollection()
    {
        $collection = new $this->collectionclass();
        $entity = $this->getExample();
        $organisationList = new \BO\Zmsentities\Collection\OrganisationList();
        $organisationList->addEntity((new \BO\Zmsentities\Organisation())->getExample());
        $entity->organisations = $organisationList;
        $collection->addEntity($entity);
        $this->assertEntityList($this->entityclass, $collection);
        $this->assertTrue(
            1 == count($collection),
            'Missing new Owner Entity with ID ' . $entity->id . ' in collection, 1 expected (' .
            count($collection) . ' found)'
            );

        $organisationList = $collection->getOrganisationsByOwnerId(7);
        $this->assertTrue(456 == $organisationList[0]->id, 'Getting organisation by owner failed');

        $organisationList = $collection->toDepartmentListByOrganisationName();
        $this->assertTrue(
            array_key_exists('Berlin-Brandenburg', $organisationList),
            'Get list with named index failed'
        );
    }
}
