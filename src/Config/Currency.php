<?php

namespace App\Config;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case JPY = 'JPY';
    case GBP = 'GBP';
    case CHF = 'CHF';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case NZD = 'NZD';
}