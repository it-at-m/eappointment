<?php

namespace BO\Zmsentities;

class Useraccountprofile extends Schema\Entity
{
    public const PRIMARY = 'username';

    public static $schema = 'useraccountprofile.json';

    #[\Override]
    public function getDefaults(): array
    {
        return [
            'permissions' => [],
        ];
    }
}
