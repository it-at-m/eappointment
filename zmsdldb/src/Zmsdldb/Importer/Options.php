<?php

namespace BO\Zmsdldb\Importer;

interface Options
{
    public const int OPTION_NONE = 0;
    public const int OPTION_CLEAR_ENTITIY_TABLE = 2;
    public const int OPTION_CLEAR_ENTITIY_REFERENCES_TABLES = 4;
}
