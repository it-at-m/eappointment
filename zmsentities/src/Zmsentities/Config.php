<?php

namespace BO\Zmsentities;

class Config extends Schema\Entity
{
    public static $schema = "config.json";

    /**
     * @return (int|string)[][]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'appointments' => [
                'urlChange' => 'https://stadt.muenchen.de/buergerservice/terminvereinbarung.html',
                'urlAppointments' => 'https://stadt.muenchen.de/buergerservice/terminvereinbarung.html',
            ],
            'changelog' => [
                'urlReleaseNotes' => 'https://it-at-m.github.io/eappointment/overview/changelog.html'
            ],
            'ticketprinter' => [
                'baseUrl' => '/terminvereinbarung/ticketprinter/'
            ],
            'calldisplay' => [
                'baseUrl' => '/terminvereinbarung/calldisplay/'
            ],
            'emergency' => [
                'refreshInterval' => 5
            ],
            'support' => [
                'eMail' => 'noreply-terminvereinbarung@muenchen.de',
                'telephone' => '089/233-96000'
            ]
        ];
    }

    public function hasType(mixed $type): bool
    {
        return (isset($this[$type])) ? true : false;
    }

    public function hasPreference(mixed $type, mixed $key): bool
    {
        return ($this->hasType($type) && isset($this[$type][$key])) ? true : false;
    }

    public function getPreference(mixed $type, mixed $key): mixed
    {
        return $this->toProperty()->$type->$key->get();
    }

    public function setPreference(mixed $type, mixed $key, mixed $value): static
    {
        $preference = $this->toProperty()->$type->$key->get();
        if (null !== $preference) {
            $this[$type][$key] = $value;
        }
        return $this;
    }
}
