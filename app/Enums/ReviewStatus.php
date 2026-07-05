<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
}
