<?php

namespace App\Enums;

enum BuildStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case RolledBack = 'rolled_back';
}
