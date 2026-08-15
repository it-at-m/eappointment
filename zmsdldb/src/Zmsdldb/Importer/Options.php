<?php

namespace BO\Zmsdldb\Importer;

interface Options
{
    const int OPTION_NONE = 0;
    const int OPTION_CLEAR_ENTITIY_TABLE = 2;
    const int OPTION_CLEAR_ENTITIY_REFERENCES_TABLES = 4;
}
