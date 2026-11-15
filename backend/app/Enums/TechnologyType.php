<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TechnologyType: string implements HasLabel
{
    case Language = 'language';
    case Tool = 'tool';
    case Platform = 'platform';
    case Concept = 'concept';

    public function getLabel(): string
    {
        return match ($this) {
            self::Language => 'Language',
            self::Tool => 'Tool',
            self::Platform => 'Platform',
            self::Concept => 'Concept',
        };
    }
}
