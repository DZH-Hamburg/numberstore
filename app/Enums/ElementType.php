<?php

namespace App\Enums;

enum ElementType: string
{
    case Scrape = 'scrape';
    case Screenshot = 'screenshot';
    case Number = 'number';
    case Report = 'report';
}
