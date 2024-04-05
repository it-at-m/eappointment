<?php

namespace BO\Zmsentities;

use \BO\Mellon\Validator;
use \BO\Zmsentities\Helper\Property;

class Ticketprinter extends Schema\Entity
{
    const PRIMARY = 'hash';

    public static $schema = "ticketprinter.json";

    protected $allowedButtonTypes = [
        's' => 'scope',
        /*'c' => 'cluster',*/
        'l' => 'link',
        'r' => 'request'
    ];

    public function getDefaults()
    {
        return [
            'enabled' => false,
            'reload' => 30
        ];
    }

    public function getHashWith($organisiationId)
    {
        $this->hash = $organisiationId . bin2hex(openssl_random_pseudo_bytes(16));
        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function toStructuredButtonList()
    {
        $ticketprinter = clone $this;
        $ticketprinter->buttons = array();
        $buttonList = explode(',', $ticketprinter->buttonlist);
        foreach ($buttonList as $string) {
            $button = array();
            $button = $ticketprinter->getValidButtonWithType($string);
            $ticketprinter->buttons[] = $ticketprinter->getButtonData($string, $button);
        }
        return $ticketprinter;
    }

    public function getScopeList()
    {
        $scopeList = new Collection\ScopeList();
        if ($this->toProperty()->buttons->isAvailable()) {
            foreach ($this->buttons as $button) {
                if (in_array($button['type'], ['scope', 'request'])) {
                    $scopeList->addEntity(new Scope($button['scope']));
                }
            }
        }
        return $scopeList;
    }

    public function getClusterList()
    {
        $clusterList = new Collection\ClusterList();
        if ($this->toProperty()->buttons->isAvailable()) {
            foreach ($this->buttons as $button) {
                if ('cluster' == $button['type']) {
                    $clusterList->addEntity(new Cluster($button['cluster']));
                }
            }
        }
        return $clusterList;
    }

    protected function getValidButtonWithType($string)
    {
        $type = $this->getButtonType($string);
        $value = $this->getButtonValue($string, $type);
        if (! Property::__keyExists($type, $this->allowedButtonTypes) || ! $value) {
            throw new Exception\TicketprinterUnvalidButton();
        }
        return array(
            'type' => $this->allowedButtonTypes[$type]
        );
    }

    protected function getButtonData($string, $button)
    {
        $value = $this->getButtonValue($string, $this->getButtonType($string));
        if ('link' == $button['type']) {
            $button = $this->getExternalLinkData($value, $button);
        } else {
            $button['url'] = '/'. $button['type'] .'/'. $value .'/';
            $button[$button['type']]['id'] = $value;
        }

        if ('request' == $button['type']) {
            $button['scope'] = [
                'id' => explode('-', $value)[0]
            ];
        }

        return $button;
    }

    protected function getButtonValue($string, $type)
    {
        $value = (in_array($type, ['l', 'r'])) ?
            Validator::value(substr($string, 1))->isString() :
            Validator::value(substr($string, 1))->isNumber();
        return $value->getValue();
    }

    protected function getButtonType($string)
    {
        return substr($string, 0, 1);
    }

    protected function getExternalLinkData($value, $button)
    {
        if (preg_match("/\[([^\]]*)\]/", $value, $matches)) {
            $data = explode('|', $matches[1]);
            $button['url'] = (isset($data[0])) ? $data[0] : '';
            $button['name'] = (isset($data[1])) ? $data[1] : "Information";
        }
        return $button;
    }
}
