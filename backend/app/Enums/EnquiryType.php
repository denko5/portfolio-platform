<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum EnquiryType: string implements HasLabel
{
    case Employment = 'employment';
    case SoftwareDevelopment = 'software_development';
    case DataAnalytics = 'data_analytics';
    case ItSupport = 'it_support';
    case Consulting = 'consulting';
    case Collaboration = 'collaboration';
    case General = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::Employment => 'Employment',
            self::SoftwareDevelopment => 'Software development',
            self::DataAnalytics => 'Data analytics',
            self::ItSupport => 'IT support',
            self::Consulting => 'Consulting',
            self::Collaboration => 'Collaboration',
            self::General => 'General enquiry',
        };
    }
}
