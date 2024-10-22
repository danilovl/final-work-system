<?php
declare(strict_types=1);

namespace App\Application\ElasticApm;

enum SpanTypeEnum: string
{
    case CONTROLLER = 'controller';
}
