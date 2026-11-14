<?php

namespace App\Enums;

enum BuildTrigger: string
{
    case ContentChange = 'content_change';
    case Manual = 'manual';
    case Rollback = 'rollback';
}
