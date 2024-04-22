<?php

namespace App\DB\Enums;

enum DBEnum: string {
    // WHERES.
    case AND_WHERE              = 'AND_WHERE';
    case OR_WHERE               = 'OR_WHERE';

    case AND_WHERE_NOT          = 'AND_WHERE_NOT';
    case OR_WHERE_NOT           = 'OR_WHERE_NOT';

    case AND_WHERE_BETWEEN      = 'AND_WHERE_BETWEEN';
    case AND_WHERE_NOT_BETWEEN  = 'AND_WHERE_NOT_BETWEEN';

    case OR_WHERE_BETWEEN       = 'OR_WHERE_BETWEEN';
    case OR_WHERE_NOT_BETWEEN   = 'OR_WHERE_NOT_BETWEEN';

    case AND_WHERE_IN           = 'AND_WHERE_IN';
    case AND_WHERE_NOT_IN       = 'AND_WHERE_NOT_IN';

    case OR_WHERE_IN            = 'OR_WHERE_IN';
    case OR_WHERE_NOT_IN        = 'OR_WHERE_NOT_IN';

    // HAVINGS.
    case AND_HAVING             = 'AND_HAVING';
    case OR_HAVING              = 'OR_HAVING';

    case AND_NOT_HAVING         = 'AND_NOT_HAVING';
    case OR_NOT_HAVING          = 'OR_NOT_HAVING';

    // not implemented.
    case AND_HAVING_BETWEEN     = 'AND_HAVING_BETWEEN';
    case OR_HAVING_BETWEEN      = 'OR_HAVING_BETWEEN';
}