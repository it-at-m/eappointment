<?php

namespace BO\Zmsdb\Query;

class Scope extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'standort';

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Provider::TABLE, 'provider'),
            'scope.InfoDienstleisterID',
            '=',
            'provider.id'
        );
        $providerQuery = new Provider($this->query);
        $providerQuery->addEntityMappingPrefixed('provider__');
        $this->query->leftJoin(
            new Alias(Department::TABLE, 'department'),
            'scope.BehoerdenID',
            '=',
            'department.BehoerdenID'
        );
        $departmentQuery = new Department($this->query);
        $departmentQuery->addEntityMappingPrefixed('department__');
        return [$providerQuery];
    }

    public function getEntityMapping()
    {
        return [
            'contact__email' => 'scope.emailstandortadmin',
            'hint' => self::expression('CONCAT(`scope`.`standortinfozeile`, " ", `scope`.`Hinweis`)'),
            'id' => 'scope.StandortID',
            'preferences__appointment__deallocationDuration' => 'scope.loeschdauer',
            'preferences__appointment__endInDaysDefault' => 'scope.Termine_bis',
            'preferences__appointment__multipleSlotsEnabled' => 'scope.mehrfachtermine',
            'preferences__appointment__reservationDuration' => 'scope.reservierungsdauer',
            'preferences__appointment__startInDaysDefault' => 'scope.Termine_ab',
            'preferences__client__alternateAppointmentUrl' => 'scope.qtv_url',
            'preferences__client__amendmentActivated' => 'scope.anmerkungPflichtfeld',
            'preferences__client__amendmentLabel' => 'scope.anmerkungLabel',
            'preferences__client__emailRequired' => 'scope.emailPflichtfeld',
            'preferences__client__telephoneActivated' => 'scope.telefonaktiviert',
            'preferences__client__telephoneRequired' => 'scope.telefonPflichtfeld',
            'preferences__notifications__confirmationContent' => 'scope.smsbestaetigungstext',
            'preferences__notifications__confirmationEnabled' => 'scope.smswmsbestaetigung',
            'preferences__notifications__enabled' => 'scope.smswarteschlange',
            'preferences__notifications__headsUpContent' => 'scope.smsbenachrichtigungstext',
            'preferences__notifications__headsUpTime' => 'scope.smsbenachrichtigungsfrist',
            'preferences__pickup__alternateName' => 'scope.ausgabeschaltername',
            'preferences__pickup__isDefault' => 'scope.defaultabholerstandort',
            'preferences__queue__callCountMax' => 'scope.anzahlwiederaufruf',
            'preferences__queue__callDisplayText' => 'scope.aufrufanzeigetext',
            'preferences__queue__firstNumber' => 'scope.startwartenr',
            'preferences__queue__lastNumber' => 'scope.endwartenr',
            'preferences__queue__processingTimeAverage' => 'scope.Bearbeitungszeit',
            'preferences__queue__publishWaitingTimeEnabled' => 'scope.wartezeitveroeffentlichen',
            'preferences__queue__statisticsEnabled' =>  self::expression('NOT `scope`.`ohnestatistik`'),
            'preferences__survey__emailContent' => 'scope.kundenbef_emailtext',
            'preferences__survey__enabled' => 'scope.kundenbefragung',
            'preferences__survey__label' => 'scope.kundenbef_label',
            'preferences__ticketprinter__deactivatedText' => 'scope.wartenrhinweis',
            'preferences__ticketprinter__notificationsAmendmentEnabled' => 'scope.smsnachtrag',
            'preferences__ticketprinter__notificationsDelay' => 'scope.smskioskangebotsfrist',
            'preferences__workstation__emergencyEnabled' => 'scope.notruffunktion',
            'shortName' => 'scope.standortkuerzel',
            'status__emergency__acceptedByWorkstation' => 'scope.notrufantwort',
            'status__emergency__activated' => 'scope.notrufausgeloest',
            'status__emergency__calledByWorkstation' => 'scope.notrufinitiierung',
            'status__queue__ghostWorkstationCount' => 'scope.virtuellesachbearbeiterzahl',
            'status__queue__givenNumberCount' => 'scope.vergebenewartenummern',
            'status__queue__lastGivenNumber' => 'scope.letztewartenr',
            'status__queue__lastGivenNumberTimestamp' => 'scope.wartenrdatum',
            'status__ticketprinter__deactivated' => 'scope.wartenrsperre',
        ];
    }

    public function getReferenceMapping()
    {
        return [
            'department__$ref' => self::expression('CONCAT("/department/", `scope`.`BehoerdenID`, "/")'),
            'provider__$ref' => self::expression('CONCAT("/provider/", `scope`.`InfoDienstleisterID`, "/")'),
        ];
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('scope.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where('scope.InfoDienstleisterID', '=', $providerId);
        return $this;
    }

    public function addConditionClusterId($clusterId)
    {
        $this->query->leftJoin(
            new Alias('clusterzuordnung', 'cluster_scope'),
            'scope.StandortID',
            '=',
            'cluster_scope.standortID'
        );
        $this->query->where('cluster_scope.clusterID', '=', $clusterId);
        return $this;
    }
}
