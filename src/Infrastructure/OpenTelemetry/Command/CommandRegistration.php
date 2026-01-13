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

namespace App\Infrastructure\OpenTelemetry\Command;

use App\Infrastructure\OpenTelemetry\Command\Contex\ConsoleEnvPropagationGetterSetter;
use App\Infrastructure\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode};
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class CommandRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        hook(Command::class, 'run', pre: self::getPreCallback(), post: self::getPostCallback());
    }

    private static function getPreCallback(): callable
    {
        return static function (Command $command, array $params, string $class, string $function): void {
            $spanName = $command->getName();
            if ($spanName === 'messenger:consume') {
                return;
            }

            $instrumentation = new CachedInstrumentation(__FILE__);
            $parentContext = Globals::propagator()->extract(getenv(), ConsoleEnvPropagationGetterSetter::instance());

            if ($spanName === null || $spanName === '') {
                $spanName = $class . '::' . $function;
            }
            $spanName = sprintf('bin/console %s', $spanName);

            $span = $instrumentation->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_SERVER)
                ->setParent($parentContext)
                ->setAttributes([
                    TraceAttributes::CODE_FUNCTION => $function,
                    TraceAttributes::CODE_NAMESPACE => $class,
                    'type' => 'console-command',
                    'console.command.class' => $command::class
                ])
                ->addLink(Span::fromContext($parentContext)->getContext())
                ->startSpan();

            $context = $span->storeInContext(Context::getCurrent());
            Globals::propagator()->inject($ignoredVar, ConsoleEnvPropagationGetterSetter::instance(), $context);
            Context::storage()->attach($context);
        };
    }

    private static function getPostCallback(): callable
    {
        return static function (Command $command, array $params, $exitCode, ?Throwable $exception): void {
            if ($command->getName() === 'messenger:consume') {
                return;
            }

            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());
            $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

            $status = $exitCode !== 0 || $exception !== null ? StatusCode::STATUS_ERROR : StatusCode::STATUS_OK;

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
            }

            $exitCode = (int) $exitCode;
            $span->setAttribute('exit.code', $exitCode);
            $span->setStatus($status);
            $span->end();
        };
    }
}
