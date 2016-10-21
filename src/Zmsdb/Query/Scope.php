<?php

namespace BO\Zmsdb\Query;

class Scope extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'standort';

    const QUERY_BY_DEPARTMENTID = 'SELECT
            scope.`StandortID` AS id
        FROM `zmsbo`.`standort` scope
        WHERE
            scope.`BehoerdenID` = :department_id
    ';

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Provider::getTablename(), 'provider'),
            'scope.InfoDienstleisterID',
            '=',
            'provider.id'
        );
        $providerQuery = new Provider($this->query, 'provider__');


        //$this->query->leftJoin(
        //    new Alias(Department::TABLE, 'department'),
        //    'scope.BehoerdenID',
        //    '=',
        //    'department.BehoerdenID'
        //);
        //$departmentQuery = new Department($this->query, 'department__');

        //return [$providerQuery, $departmentQuery];
        return [$providerQuery];
    }

    public function getEntityMapping()
    {
        return [
            'hint' => self::expression('CONCAT(`scope`.`standortinfozeile`, " | ", `scope`.`Hinweis`)'),
            'id' => 'scope.StandortID',
            'contact__name' => 'scope.Bezeichnung',
            'contact__street' => 'scope.Adresse',
            'contact__email' => 'scope.emailstandortadmin',
            'contact__country' => self::expression('"Germany"'),
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
            'preferences__notifications__enabled' => 'scope.smswarteschlange',
            'preferences__notifications__headsUpContent' => 'scope.smsbenachrichtigungstext',
            'preferences__notifications__headsUpTime' => 'scope.smsbenachrichtigungsfrist',
            'preferences__pickup__alternateName' => 'scope.ausgabeschaltername',
            'preferences__pickup__isDefault' => 'scope.defaultabholerstandort',
            'preferences__queue__callCountMax' => 'scope.anzahlwiederaufruf',
            'preferences__queue__callDisplayText' => 'scope.aufrufanzeigetext',
            'preferences__queue__firstNumber' => 'scope.startwartenr',
            'preferences__queue__lastNumber' => 'scope.endwartenr',
            'preferences__queue__processingTimeAverage' => self::expression(
                'FLOOR(TIME_TO_SEC(`scope`.`Bearbeitungszeit`) / 60)'
            ),
            'preferences__queue__publishWaitingTimeEnabled' => 'scope.wartezeitveroeffentlichen',
            'preferences__queue__statisticsEnabled' =>  self::expression('NOT `scope`.`ohnestatistik`'),
            'preferences__survey__emailContent' => 'scope.kundenbef_emailtext',
            'preferences__survey__enabled' => 'scope.kundenbefragung',
            'preferences__survey__label' => 'scope.kundenbef_label',
            'preferences__ticketprinter__confirmationEnabled' => 'scope.smswmsbestaetigung',
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
            'provider__id' => 'scope.InfoDienstleisterID',
            //'department__id' => 'scope.BehoerdenID'
        ];
    }

    public function getReferenceMapping()
    {
        return [
            //'department__$ref' => self::expression('CONCAT("/department/", `scope`.`BehoerdenID`, "/")'),
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

    public function addConditionDepartmentId($departmentId)
    {
        $this->query->where('scope.BehoerdenID', '=', $departmentId);
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

    public function reverseEntityMapping(\BO\Zmsentities\Scope $entity, $parentId = null)
    {
        $data = array();
        if ($parentId) {
            $data['BehoerdenID'] = $parentId;
        }
        $data['InfoDienstleisterID'] = $entity->getProviderId();
        $data['emailstandortadmin'] = $entity->getContactEMail();
        $data['standortinfozeile'] = $entity->getScopeInfo();
        $data['Hinweis'] = $entity->getScopeHint();
        $data['Bezeichnung'] = $entity->getName();
        $data['standortkuerzel'] = $entity->shortName;
        $data['Adresse'] = $entity->contact['street'];
        $data['loeschdauer'] = $entity->getPreference('appointment', 'deallocationDuration');
        $data['Termine_bis'] = $entity->getPreference('appointment', 'endInDaysDefault');
        $data['Termine_ab'] = $entity->getPreference('appointment', 'startInDaysDefault');
        $data['mehrfachtermine'] = $entity->getPreference('appointment', 'multipleSlotsEnabled', true);
        $data['reservierungsdauer'] = $entity->getPreference('appointment', 'reservationDuration');
        $data['qtv_url'] = $entity->getPreference('client', 'alternateAppointmentUrl');
        $data['anmerkungPflichtfeld'] = $entity->getPreference('client', 'amendmentActivated', true);
        $data['anmerkungLabel'] = $entity->getPreference('client', 'amendmentLabel', true);
        $data['emailPflichtfeld'] = $entity->getPreference('client', 'emailRequired', true);
        $data['telefonaktiviert'] = $entity->getPreference('client', 'telephoneActivated', true);
        $data['telefonPflichtfeld'] = $entity->getPreference('client', 'telephoneRequired', true);
        $data['smsbestaetigungstext'] = $entity->getPreference('notifications', 'confirmationContent');
        $data['smswarteschlange'] = $entity->getPreference('notifications', 'enabled', true);
        $data['smsbenachrichtigungstext'] = $entity->getPreference('notifications', 'headsUpContent');
        $data['smsbenachrichtigungsfrist'] = $entity->getPreference('notifications', 'headsUpTime');
        $data['ausgabeschaltername'] = $entity->getPreference('pickup', 'alternateName');
        $data['defaultabholerstandort'] = $entity->getPreference('pickup', 'isDefault', true);
        $data['anzahlwiederaufruf'] = $entity->getPreference('queue', 'callCountMax');
        $data['aufrufanzeigetext'] = $entity->getPreference('queue', 'callDisplayText');
        $data['startwartenr'] = $entity->getPreference('queue', 'firstNumber');
        $data['endwartenr'] = $entity->getPreference('queue', 'lastNumber');
        $data['Bearbeitungszeit'] = gmdate("H:i", $entity->getPreference('queue', 'processingTimeAverage') * 60);
        $data['wartezeitveroeffentlichen'] = $entity->getPreference('queue', 'publishWaitingTimeEnabled', true);
        $data['ohnestatistik'] = (0 == $entity->getPreference('queue', 'statisticsEnabled')) ? 1 : 0;
        $data['kundenbef_emailtext'] = $entity->getPreference('survey', 'emailContent');
        $data['kundenbefragung'] = $entity->getPreference('survey', 'enabled', true);
        $data['kundenbef_label'] = $entity->getPreference('survey', 'label');
        $data['smswmsbestaetigung'] = $entity->getPreference('ticketprinter', 'confirmationEnabled', true);
        $data['wartenrhinweis'] = $entity->getPreference('ticketprinter', 'deactivatedText');
        $data['smsnachtrag'] = $entity->getPreference('ticketprinter', 'notificationsAmendmentEnabled', true);
        $data['smskioskangebotsfrist'] = $entity->getPreference('ticketprinter', 'notificationsDelay');
        $data['notruffunktion'] = $entity->getPreference('workstation', 'emergencyEnabled', true);
        $data['notrufantwort'] = $entity->getStatus('emergency', 'acceptedByWorkstation');
        $data['notrufausgeloest'] = $entity->getStatus('emergency', 'activated');
        $data['notrufinitiierung'] = $entity->getStatus('emergency', 'calledByWorkstation');
        $data['virtuellesachbearbeiterzahl'] = $entity->getStatus('queue', 'ghostWorkstationCount');
        $data['vergebenewartenummern'] = $entity->getStatus('queue', 'givenNumberCount');
        $data['letztewartenr'] = $entity->getStatus('queue', 'lastGivenNumber');
        $lastGivenTimestamp = $entity->getStatus('queue', 'lastGivenNumberTimestamp');
        $data['wartenrdatum'] = ($lastGivenTimestamp) ? date('Y-m-d', $lastGivenTimestamp) : null;
        $data['wartenrsperre'] = $entity->getStatus('ticketprinter', 'deactivated');

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }

    public function postProcess($data)
    {
        $data["status__queue__lastGivenNumberTimestamp"] =
            (new \DateTime($data["status__queue__lastGivenNumberTimestamp"]))->getTimestamp();
        return $data;
    }
}
