<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LinkType: string implements HasLabel
{
    case Github = 'github';
    case Live = 'live';
    case Notebook = 'notebook';
    case Drive = 'drive';
    case Report = 'report';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Github => 'GitHub repository',
            self::Live => 'Live demo',
            self::Notebook => 'Notebook',
            self::Drive => 'Drive folder',
            self::Report => 'Report',
            self::Other => 'Other',
        };
    }
}
