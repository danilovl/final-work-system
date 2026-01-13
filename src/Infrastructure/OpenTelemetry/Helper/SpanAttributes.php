<?php declare(strict_types=1);

/**
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Infrastructure\OpenTelemetry\Helper;

enum SpanAttributes: string
{
    case TRACE_ID = 'traceId';
    case SPAN_ID = 'spanId';
    case SPAN_TYPE = 'type';
    case RECORDED_LOCATION = 'recordedLocation';
}
