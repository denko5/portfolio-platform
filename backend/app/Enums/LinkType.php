<?php

namespace App\Enums;

enum LinkType: string
{
    case Github = 'github';
    case Live = 'live';
    case Notebook = 'notebook';
    case Drive = 'drive';
    case Report = 'report';
    case Other = 'other';
}
