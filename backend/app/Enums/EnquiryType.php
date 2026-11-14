<?php

namespace App\Enums;

enum EnquiryType: string
{
    case Employment = 'employment';
    case SoftwareDevelopment = 'software_development';
    case DataAnalytics = 'data_analytics';
    case ItSupport = 'it_support';
    case Consulting = 'consulting';
    case Collaboration = 'collaboration';
    case General = 'general';
}
