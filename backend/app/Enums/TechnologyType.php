<?php

namespace App\Enums;

enum TechnologyType: string
{
    case Language = 'language';
    case Tool = 'tool';
    case Platform = 'platform';
    case Concept = 'concept';
}
