<?php

namespace BO\Zmsapi\Helper;


class MailTemplateProvider 
{
    protected $templates = false;

    public function getTemplate($templateName) {
        if (!$this->templates) {
            $this->loadTemplates();
        }
        return $this->templates[$templateName];
    }

    public function getTemplates() {
        if (!$this->templates) {
            $this->loadTemplates();
        }
        return $this->templates;
    }

    protected function loadTemplates() {

        $this->templates = array(
            'notification_appointment.twig'=> "template is empty",
            'notification_confirmation.twig'=> "template is empty",
            'notification_confirmation.twig'=> "template is empty",
            'notification_headsup.twig'=> "template is empty",
            'notification_reminder.twig'=> "template is empty",
            'notification_pickup.twig'=> "template is empty",
            'notification_deleted.twig' => "template is empty",
            'mail_queued.twig'=> "template is empty",
            'mail_confirmation.twig'=> "template is empty",
            'mail_reminder.twig'=> "template is empty",
            'mail_pickup.twig'=> "template is empty",
            'mail_delete.twig'=> "template is empty",
            'mail_delete.twig'=> "template is empty",
            'mail_survey.twig'=> "template is empty",
            'mail_processlist_overview.twig'=> "template is empty",
            'mail_preconfirmed.twig' => "template is empty",
            'icsappointment.twig'=> "template is empty",
            'icsappointment_delete.twig' => "template is empty",
            'mail_admin_delete.twig'=> "template is empty",
            'mail_admin_delete.twig'=> "template is empty",
            'mail_admin_update.twig' => "template is empty",
        );


        $result = (new \BO\Zmsdb\MailTemplates())->readList();
        foreach($result as $templateObject) {
            $this->templates[$templateObject['name']] = $templateObject['value'];
        }


    }



}