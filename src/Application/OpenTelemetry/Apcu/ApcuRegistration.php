<?php declare(strict_types=1);

/*
 *
 * This file is part of the FinalWorkSystem project.
 * (c) Vladimir Danilov
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace App\Application\OpenTelemetry\Apcu;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\CounterInterface;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration')]
class ApcuRegistration implements OpenTelemetryRegistrationInterface
{
    public function registration(): void
    {
        $apcuOperations = [
            'apcu_add',
            'apcu_cache_info',
            'apcu_cas',
            'apcu_clear_cache',
            'apcu_dec',
            'apcu_delete',
            'apcu_enabled',
            'apcu_entry',
            'apcu_exists',
            'apcu_fetch',
            'apcu_inc',
            'apcu_key_info',
            'apcu_sma_info',
            'apcu_store'
        ];

        foreach ($apcuOperations as $apcuOperation) {
            $counters = new stdClass;

            hook(
                null,
                $apcuOperation,
                pre: static function () use ($apcuOperation, $counters): void {
                    $counter = $counters->{$apcuOperation} ?? null;

                    if ($counter instanceof CounterInterface === false) {
                        $counter = self::counter($apcuOperation, 'calls');
                        $counters->{$apcuOperation} = $counter;
                    }

                    $counter->add(1);
                }
            );
        }
    }

    private static function counter(string $name, ?string $unit = null): CounterInterface
    {
        return Globals::meterProvider()
            ->getMeter(__CLASS__)
            ->createCounter($name, $unit);
    }
}
