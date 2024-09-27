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
        FROM `standort` scope
        WHERE
            scope.`BehoerdenID` = :department_id
    ';

    public function getQueryLastWaitingNumber()
    {
        return '
            SELECT letztewartenr
            FROM `standort` scope
            WHERE scope.`StandortID` = :scope_id LIMIT 1 FOR UPDATE';
    }

    public function getQueryGivenNumbersInContingent()
    {
        return '
            SELECT *
            FROM `standort` scope
            WHERE
                scope.`StandortID` = :scope_id AND
                IF(
                    scope.`wartenummernkontingent` > 0,
                    IF(
                        isNull(scope.`vergebenewartenummern`), 
                        0, 
                        scope.`vergebenewartenummern`
                    ) < scope.`wartenummernkontingent`,
                    scope.`StandortID`
                )
        ';
    }

    public function getQuerySimpleClusterMatch()
    {
        return '
            SELECT standortID AS id
            FROM `clusterzuordnung`
            WHERE
                clusterID = ?
        ';
    }

    public function getQuerySimpleDepartmentMatch()
    {
        return '
            SELECT s.StandortID AS id, p.name AS contact__name
            FROM `standort` AS s
            LEFT JOIN `provider` AS p ON s.InfoDienstleisterID = p.id
                AND p.source = s.source
            WHERE
                s.BehoerdenID = ?
        ';
    }

    public function getQueryReadImageData()
    {
        return '
            SELECT `imagecontent`, `imagename`
            FROM `imagedata`
            WHERE
                `imagename` LIKE :imagename
        ';
    }

    public function getQueryWriteImageData()
    {
        return '
            REPLACE INTO `imagedata`
            SET
                imagename=:imagename,
                imagecontent=:imagedata
        ';
    }

    public function getQueryDeleteImage()
    {
        return '
            DELETE FROM `imagedata`
            WHERE
                `imagename` LIKE :imagename
        ';
    }

    public function addJoin()
    {
        $this->leftJoin(
            new Alias(Provider::getTablename(), 'provider'),
            self::expression('scope.InfoDienstleisterID = provider.id && scope.source = provider.source')
        );
        $providerQuery = new Provider($this, $this->getPrefixed('provider__'));
        return [$providerQuery];
    }

    protected function addRequiredJoins()
    {
        $this->leftJoin(
            new Alias(Department::TABLE, 'scopedepartment'),
            'scope.BehoerdenID',
            '=',
            'scopedepartment.BehoerdenID'
        );
        $this->leftJoin(
            new Alias('sms', 'scopesms'),
            'scopedepartment.BehoerdenID',
            '=',
            'scopesms.BehoerdenID'
        );
        $this->leftJoin(
            new Alias('email', 'scopemail'),
            'scopedepartment.BehoerdenID',
            '=',
            'scopemail.BehoerdenID'
        );
        $this->leftJoin(
            new Alias(Provider::getTablename(), 'scopeprovider'),
            self::expression('scope.InfoDienstleisterID = scopeprovider.id && scope.source = scopeprovider.source')
        );
    }

    //Todo: now() Parameter to enable query cache
    public function getEntityMapping()
    {
        return [
            'hint' => 'scope.Hinweis',
            'id' => 'scope.StandortID',
            'contact__name' => 'scopeprovider.name',
            'contact__street' => 'scope.Adresse',
            'contact__email' => 'scope.emailstandortadmin',
            'contact__country' => self::expression('"Germany"'),
            'lastChange' => 'scope.updateTimestamp',
            'preferences__appointment__deallocationDuration' => 'scope.loeschdauer',
            'preferences__appointment__infoForAppointment' => 'scope.info_for_appointment',
            'preferences__appointment__endInDaysDefault' => 'scope.Termine_bis',
            'preferences__appointment__multipleSlotsEnabled' => 'scope.mehrfachtermine',
            'preferences__appointment__reservationDuration' => 'scope.reservierungsdauer',
            'preferences__appointment__activationDuration' => 'scope.aktivierungsdauer',
            'preferences__appointment__startInDaysDefault' => 'scope.Termine_ab',
            'preferences__appointment__notificationConfirmationEnabled' =>
                self::expression('scopesms.enabled && scopesms.Absender != "" && scopesms.internetbestaetigung'),
            'preferences__appointment__notificationHeadsUpEnabled' =>
                self::expression('scopesms.enabled && scopesms.Absender != "" && scopesms.interneterinnerung'),
            'preferences__client__alternateAppointmentUrl' => 'scope.qtv_url',
            'preferences__client__amendmentActivated' => 'scope.anmerkungPflichtfeld',
            'preferences__client__amendmentLabel' => 'scope.anmerkungLabel',
            'preferences__client__emailFrom' => 'scopemail.absenderadresse',
            'preferences__client__emailRequired' => 'scope.emailPflichtfeld',
            'preferences__client__emailConfirmationActivated' => 'scope.email_confirmation_activated',
            'preferences__client__telephoneActivated' => 'scope.telefonaktiviert',
            'preferences__client__telephoneRequired' => 'scope.telefonPflichtfeld',
            'preferences__client__appointmentsPerMail' => 'scope.appointments_per_mail',
            'preferences__client__slotsPerAppointment' => 'scope.slots_per_appointment',
            'preferences__logs__deleteLogsOlderThanDays' => 'scope.delete_logs_older_than_days',
            'preferences__client__whitelistedMails' => 'scope.whitelisted_mails',
            'preferences__client__customTextfieldActivated' => 'scope.custom_text_field_active',
            'preferences__client__customTextfieldRequired' => 'scope.custom_text_field_required',
            'preferences__client__customTextfieldLabel' => 'scope.custom_text_field_label',
            'preferences__client__captchaActivatedRequired' => 'scope.captcha_activated_required',
            'preferences__client__adminMailOnAppointment' => 'scope.admin_mail_on_appointment',
            'preferences__client__adminMailOnDeleted' => 'scope.admin_mail_on_deleted',
            'preferences__client__adminMailOnUpdated' => 'scope.admin_mail_on_updated',
            'preferences__client__adminMailOnMailSent' => 'scope.admin_mail_on_mail_sent',
            'preferences__notifications__confirmationContent' => 'scope.smsbestaetigungstext',
            'preferences__notifications__headsUpContent' => 'scope.smsbenachrichtigungstext',
            'preferences__notifications__headsUpTime' => 'scope.smsbenachrichtigungsfrist',
            'preferences__pickup__alternateName' => 'scope.ausgabeschaltername',
            'preferences__pickup__isDefault' => 'scope.defaultabholerstandort',
            'preferences__queue__callCountMax' => 'scope.anzahlwiederaufruf',
            'preferences__queue__callDisplayText' => 'scope.aufrufanzeigetext',
            'preferences__queue__firstNumber' => 'scope.startwartenr',
            'preferences__queue__lastNumber' => 'scope.endwartenr',
            'preferences__queue__maxNumberContingent' => 'scope.wartenummernkontingent',
            'preferences__queue__processingTimeAverage' => self::expression(
                'FLOOR(TIME_TO_SEC(`scope`.`Bearbeitungszeit`) / 60)'
            ),
            'preferences__queue__publishWaitingTimeEnabled' => 'scope.wartezeitveroeffentlichen',
            'preferences__queue__statisticsEnabled' => self::expression('NOT `scope`.`ohnestatistik`'),
            'preferences__survey__emailContent' => 'scope.kundenbef_emailtext',
            'preferences__survey__enabled' => 'scope.kundenbefragung',
            'preferences__survey__label' => 'scope.kundenbef_label',
            'preferences__ticketprinter__buttonName' => self::expression(
                'IF(`scope`.`standortinfozeile`!="", `scope`.`standortinfozeile`, `scope`.`Bezeichnung`)'
            ),
            'preferences__ticketprinter__confirmationEnabled' => 'scope.smswmsbestaetigung',
            'preferences__ticketprinter__deactivatedText' => 'scope.wartenrhinweis',
            'preferences__ticketprinter__notificationsAmendmentEnabled' => 'scope.smsnachtrag',
            'preferences__ticketprinter__notificationsEnabled' => 'scope.smswarteschlange',
            'preferences__ticketprinter__notificationsDelay' => 'scope.smskioskangebotsfrist',
            'preferences__workstation__emergencyEnabled' => 'scope.notruffunktion',
            'preferences__workstation__emergencyRefreshInterval' => self::expression(
                '(SELECT `value` FROM config WHERE `name`="emergency__refreshInterval")'
            ),
            'shortName' => 'scope.standortkuerzel',
            'status__emergency__acceptedByWorkstation' => 'scope.notrufantwort',
            'status__emergency__activated' => 'scope.notrufausgeloest',
            'status__emergency__calledByWorkstation' => 'scope.notrufinitiierung',
            'status__queue__ghostWorkstationCount' => 'scope.virtuellesachbearbeiterzahl',
            'status__queue__givenNumberCount' => 'scope.vergebenewartenummern',
            'status__queue__lastGivenNumber' => 'scope.letztewartenr',
            'status__queue__lastGivenNumberTimestamp' => 'scope.wartenrdatum',
            'status__ticketprinter__deactivated' => 'scope.wartenrsperre',
            'provider__id' => self::expression(
                'IF(`scopeprovider`.`id`!="", `scopeprovider`.`id`, `scope`.`InfoDienstleisterID`)'
            ),
            'provider__source' => self::expression(
                'IF(`scopeprovider`.`source`!="", `scopeprovider`.`source`, `scope`.`source`)'
            ),
            'source' => 'scope.source'
        ];
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('scope.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionWithAdminEmail()
    {
        $this->query->where('scope.emailstandortadmin', '!=', '');
        return $this;
    }

    public function addSelectWorkstationCount($dateTime)
    {
        $this->query->select(
            ['status__queue__workstationCount' => self::expression('
                IF(
                    `scope`.`virtuellesachbearbeiterzahl` > -1,
                    `scope`.`virtuellesachbearbeiterzahl`,
                    (
                        SELECT COUNT(*)
                        FROM nutzer
                        WHERE nutzer.StandortID = scope.StandortID
                        AND nutzer.Datum = "'. $dateTime->format('Y-m-d') .'"
                        AND nutzer.Arbeitsplatznr <> 0
                    )
                )
            ')
            ]
        );
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
        $this->leftJoin(
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
        $data['Adresse'] = (isset($entity->contact['street'])) ? $entity->contact['street'] : '';
        $data['loeschdauer'] = $entity->getPreference('appointment', 'deallocationDuration');
        $data['info_for_appointment'] = $entity->getPreference('appointment', 'infoForAppointment');
        $data['Termine_bis'] = $entity->getPreference('appointment', 'endInDaysDefault');
        $data['Termine_ab'] = $entity->getPreference('appointment', 'startInDaysDefault');
        $data['mehrfachtermine'] = $entity->getPreference('appointment', 'multipleSlotsEnabled', true);
        // notificationConfirmationEnabled and notificationHeadsUpEnabled are saved in department!
        $data['reservierungsdauer'] = $entity->getPreference('appointment', 'reservationDuration');
        $data['aktivierungsdauer'] = $entity->getPreference('appointment', 'activationDuration');
        $data['qtv_url'] = $entity->getPreference('client', 'alternateAppointmentUrl');
        $data['anmerkungPflichtfeld'] = $entity->getPreference('client', 'amendmentActivated', true);
        $data['anmerkungLabel'] = $entity->getPreference('client', 'amendmentLabel');
        $data['emailPflichtfeld'] = $entity->getPreference('client', 'emailRequired', true);
        $data['email_confirmation_activated'] = $entity->getPreference('client', 'emailConfirmationActivated', true);
        $data['telefonaktiviert'] = $entity->getPreference('client', 'telephoneActivated', true);
        $data['telefonPflichtfeld'] = $entity->getPreference('client', 'telephoneRequired', true);
        $data['custom_text_field_active'] = $entity->getPreference('client', 'customTextfieldActivated', true);
        $data['custom_text_field_required'] = $entity->getPreference('client', 'customTextfieldRequired', true);
        $data['custom_text_field_label'] = $entity->getPreference('client', 'customTextfieldLabel');
        $data['captcha_activated_required'] = $entity->getPreference('client', 'captchaActivatedRequired');
        $data['appointments_per_mail'] = (int) $entity->getPreference('client', 'appointmentsPerMail');
        $data['slots_per_appointment'] = (int) $entity->getPreference('client', 'slotsPerAppointment');
        $data['delete_logs_older_than_days'] = (int) $entity->getPreference('logs', 'deleteLogsOlderThanDays');
        $data['info_for_appointment'] = $entity->getPreference('appointment', 'infoForAppointment');
        $data['whitelisted_mails'] = $entity->getPreference('client', 'whitelistedMails');
        $data['admin_mail_on_appointment'] = $entity->getPreference('client', 'adminMailOnAppointment', true);
        $data['admin_mail_on_deleted'] = $entity->getPreference('client', 'adminMailOnDeleted');
        $data['admin_mail_on_updated'] = $entity->getPreference('client', 'adminMailOnUpdated', true);
        $data['admin_mail_on_mail_sent'] = $entity->getPreference('client', 'adminMailOnMailSent', true);
        $data['smsbestaetigungstext'] = $entity->getPreference('notifications', 'confirmationContent');
        $data['smsbenachrichtigungstext'] = $entity->getPreference('notifications', 'headsUpContent');
        $data['smsbenachrichtigungsfrist'] = $entity->getPreference('notifications', 'headsUpTime');
        $data['ausgabeschaltername'] = $entity->getPreference('pickup', 'alternateName');
        $data['defaultabholerstandort'] = $entity->getPreference('pickup', 'isDefault', true);
        $data['anzahlwiederaufruf'] = $entity->getPreference('queue', 'callCountMax');
        $data['aufrufanzeigetext'] = $entity->getPreference('queue', 'callDisplayText', false, '');
        $data['startwartenr'] = $entity->getPreference('queue', 'firstNumber');
        $data['endwartenr'] = $entity->getPreference('queue', 'lastNumber');
        $data['wartenummernkontingent'] = $entity->getPreference('queue', 'maxNumberContingent');
        $data['Bearbeitungszeit'] = gmdate("H:i", $entity->getPreference('queue', 'processingTimeAverage') * 60);
        $data['wartezeitveroeffentlichen'] = $entity->getPreference('queue', 'publishWaitingTimeEnabled', true);
        $data['ohnestatistik'] = (0 == $entity->getPreference('queue', 'statisticsEnabled', true)) ? 1 : 0;
        $data['kundenbef_emailtext'] = $entity->getPreference('survey', 'emailContent');
        $data['kundenbefragung'] = $entity->getPreference('survey', 'enabled', true);
        $data['kundenbef_label'] = $entity->getPreference('survey', 'label');
        $data['smswmsbestaetigung'] = $entity->getPreference('ticketprinter', 'confirmationEnabled', true);
        $data['wartenrhinweis'] = $entity->getPreference('ticketprinter', 'deactivatedText', false, '');
        $data['smsnachtrag'] = $entity->getPreference('ticketprinter', 'notificationsAmendmentEnabled', true);
        $data['smswarteschlange'] = $entity->getPreference('ticketprinter', 'notificationsEnabled', true);
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
        $data['source'] = $entity->getProvider()->source;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }

    public function setEmergencyEntityMapping(\BO\Zmsentities\Scope $entity)
    {
        $data['notrufantwort'] = ($entity->toProperty()->status->emergency->acceptedByWorkstation->get(-1));
        $data['notrufausgeloest'] = intval($entity->toProperty()->status->emergency->activated->get(0));
        $data['notrufinitiierung'] = ($entity->toProperty()->status->emergency->calledByWorkstation->get(-1));
        return $data;
    }

    public function setGhostWorkstationCountEntityMapping(\BO\Zmsentities\Scope $entity, \DateTimeInterface $dateTime)
    {
        $data['virtuellesachbearbeiterzahl'] = $entity->getStatus('queue', 'ghostWorkstationCount');
        $data['datumvirtuellesachbearbeiterzahl'] = $dateTime->format('Y-m-d');
        return $data;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("status__queue__lastGivenNumberTimestamp")] =
            strtotime($data[$this->getPrefixed("status__queue__lastGivenNumberTimestamp")]);
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        if (!$data[$this->getPrefixed('preferences__client__emailFrom')]) {
            $data[$this->getPrefixed("preferences__client__emailRequired")] = 0;
        }
        if (!$data[$this->getPrefixed('preferences__client__telephoneActivated')]) {
            $data[$this->getPrefixed("preferences__client__telephoneRequired")] = 0;
        }
        if (!$data[$this->getPrefixed('preferences__client__emailConfirmationActivated')]) {
            $data[$this->getPrefixed("preferences__client__emailConfirmationActivated")] = 0;
        }
        if (!$data[$this->getPrefixed('contact__email')]) {
            $data[$this->getPrefixed("preferences__client__adminMailOnAppointment")] = 0;
            $data[$this->getPrefixed("preferences__client__adminMailOnDeleted")] = 0;
            $data[$this->getPrefixed("preferences__client__adminMailOnUpdated")] = 0;            
            $data[$this->getPrefixed("preferences__client__adminMailOnMailSent")] = 0;            
        }
        if (!$data[$this->getPrefixed('preferences__client__customTextfieldActivated')]) {
            $data[$this->getPrefixed("preferences__client__customTextfieldRequired")] = 0;
        }
        if (!$data[$this->getPrefixed('preferences__client__appointmentsPerMail')]) {
            $data[$this->getPrefixed("preferences__client__appointmentsPerMail")] = null;
        }
        if (!$data[$this->getPrefixed('preferences__client__slotsPerAppointment')]) {
            $data[$this->getPrefixed("preferences__client__slotsPerAppointment")] = null;
        }
        if (!$data[$this->getPrefixed('preferences__logs__deleteLogsOlderThanDays')]) {
            $data[$this->getPrefixed("preferences__logs__deleteLogsOlderThanDays")] = 90;
        }
        if (!$data[$this->getPrefixed('preferences__client__whitelistedMails')]) {
            $data[$this->getPrefixed("preferences__client__whitelistedMails")] = null;
        }
        return $data;
    }
}
