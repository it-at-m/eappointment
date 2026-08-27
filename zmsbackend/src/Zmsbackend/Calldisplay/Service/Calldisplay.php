<?php

namespace BO\Zmsbackend\Calldisplay\Service;

use BO\Zmsentities\Calldisplay as Entity;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Calldisplay extends \BO\Zmsbackend\Base
{
    /**
     * read Ticketprinter by comma separated buttonlist
     *
     * @param \BO\Zmsentities\Ticketprinter $ticketprinter
     * @param \DateTimeInterface $now
     *
     * @return Entity
     */
    public function readResolvedEntity(Entity $calldisplay, \DateTimeImmutable $dateTime, $resolveReferences = 0)
    {
        if ($calldisplay->hasScopeList()) {
            $scopeList = new \BO\Zmsentities\Collection\ScopeList();
            foreach ($calldisplay->scopes as $entity) {
                $query = new \BO\Zmsbackend\Scope\Service\Scope();
                $scope = $query->readEntity($entity['id'], $resolveReferences - 1);
                if (! $scope) {
                    \App::$log->warning('Calldisplay: skip missing scope id while resolving', [
                        'scopeId' => $entity['id'],
                    ]);
                    continue;
                }
                $scopeList->addEntity($scope);
            }
            $calldisplay->scopes = $scopeList;
        }
        if ($calldisplay->hasClusterList()) {
            $clusterList = new \BO\Zmsentities\Collection\ClusterList();
            foreach ($calldisplay->clusters as $entity) {
                $query = new \BO\Zmsbackend\Cluster\Service\Cluster();
                $cluster = $query->readEntity($entity['id'], $resolveReferences);
                if (! $cluster) {
                    \App::$log->warning('Calldisplay: skip missing cluster id while resolving', [
                        'clusterId' => $entity['id'],
                    ]);
                    continue;
                }
                $clusterList->addEntity($cluster);
            }
            $calldisplay->clusters = $clusterList;
        }
        $calldisplay->setServerTime($dateTime->getTimestamp());
        $calldisplay->organisation = $this->readResolvedOrganisation($calldisplay);
        $calldisplay->image = $this->readImage($calldisplay);
        $calldisplay->contact = $this->readContactData($calldisplay);
        return $calldisplay->withOutClusterDuplicates();
    }

    public function readResolvedOrganisation(Entity $entity)
    {
        $organisation = null;
        $query = new \BO\Zmsbackend\Organisation\Service\Organisation();
        $scope = $entity->getScopeList()->getFirst();
        $cluster = $entity->getClusterList()->getFirst();
        if ($scope) {
            $organisation = $query->readByScopeId($scope->id);
        } elseif ($cluster) {
            $organisation = $query->readByClusterId($cluster->id);
        }
        return $organisation;
    }

    /**
     * @return (mixed|string)[]
     *
     */
    public function readImage(Entity $entity): array
    {
        $name = $entity->getImageName();
        $image = null;
        if ($name) {
            $image = $this->getReader()
                ->fetchOne((new \BO\Zmsbackend\Calldisplay\Repository\Calldisplay(\BO\Zmsbackend\Query\Base::SELECT))
                ->getQueryImage(), ['name' => "$name%"]);
        }
        if (! $image) {
            $image = $this->getReader()
            ->fetchOne((new \BO\Zmsbackend\Calldisplay\Repository\Calldisplay(\BO\Zmsbackend\Query\Base::SELECT))
                ->getQueryImage(), ['name' => "logo.png"]);
        }

        if (! is_array($image)) {
            return [
                'name' => '',
                'data' => '',
                'mime' => '',
            ];
        }

        $mime = pathinfo($image['name'] ?? '', PATHINFO_EXTENSION);
        $image['mime'] = ($mime == 'jpg') ? 'jpeg' : $mime;
        return $image;
    }

    public function readContactData(Entity $entity): \BO\Zmsentities\Contact
    {
        $contact = new \BO\Zmsentities\Contact();
        $contactNames = [];
        if ($entity->hasClusterList()) {
            foreach ($entity->getClusterList() as $cluster) {
                $contactNames[] = $cluster->name;
            }
        } elseif ($entity->hasScopeList()) {
            foreach ($entity->getScopeList() as $scope) {
                $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($scope->id);
                $contactNames[] = $department->name;
            }
        }

        $contactNames = array_unique($contactNames);
        $contact->name = count($contactNames) > 1 ? '' : $contactNames[0];

        return $contact;
    }
}
