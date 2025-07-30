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

namespace App\Application\OpenTelemetry\Redis;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\SemConv\{
    TraceAttributes,
    TraceAttributeValues
};
use Predis\Command\CommandInterface;
use Predis\Command\Redis\AUTH;
use Predis\Connection\{
    ConnectionInterface,
    ParametersInterface
};
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration')]
class RedisRegistration implements OpenTelemetryRegistrationInterface
{
    public function registration(): void
    {
        $instrumentation = new CachedInstrumentation(__CLASS__);
        $connectionParameters = new stdClass;

        $this->hook($instrumentation, $connectionParameters);
    }

    private function hook(CachedInstrumentation $instrumentation, stdClass $connectionParameters): void
    {
        hook(
            ConnectionInterface::class,
            '__construct',
            pre: static function (ConnectionInterface $client, array $params) use ($connectionParameters): void {
                $parameters = $params[0] ?? null;

                if (!$parameters instanceof ParametersInterface) {
                    return;
                }

                $parameters = $parameters->toArray();
                $connectionParameters->scheme = $parameters['scheme'] ?? null;
                $connectionParameters->host = $parameters['host'] ?? null;
                $connectionParameters->port = $parameters['port'] ?? null;
                $connectionParameters->database = $parameters['database'] ?? null;
            }
        );

        hook(
            ConnectionInterface::class,
            'executeCommand',
            pre: static function (ConnectionInterface $client, array $params, string $class, string $function) use ($instrumentation, $connectionParameters): void {
                [$command] = $params;

                assert($command instanceof CommandInterface);

                $spanName = self::makeSpanName($command);
                $spanName = sprintf('REDIS %s', $spanName);

                $ignore = [
                    'DoctrineNamespaceCacheKey',
                    'DoctrineSecondLevelCache'
                ];

                foreach ($ignore as $value) {
                    if (str_contains($spanName, $value)) {
                        return;
                    }
                }

                $spanBuilder = $instrumentation
                    ->tracer()
                    ->spanBuilder($spanName)
                    ->setSpanKind(SpanKind::KIND_INTERNAL)
                    ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                    ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                    ->setAttribute(TraceAttributes::SERVER_ADDRESS, $connectionParameters->host)
                    ->setAttribute(TraceAttributes::NETWORK_TRANSPORT, $connectionParameters->scheme)
                    ->setAttribute(TraceAttributes::NETWORK_PEER_ADDRESS, $connectionParameters->host)
                    ->setAttribute(TraceAttributes::NETWORK_PEER_PORT, $connectionParameters->port)
                    ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_REDIS)
                    ->setAttribute(TraceAttributes::DB_NAMESPACE, $connectionParameters->database)
                    ->setAttribute(TraceAttributes::DB_NAMESPACE, $connectionParameters->database)
                    ->setAttribute(TraceAttributes::DB_OPERATION_NAME, $command->getId())
                    ->setAttribute(TraceAttributes::DB_QUERY_TEXT, self::makeDbStatement($command))
                    ->setAttribute('command.class', $command::class);

                $parent = Context::getCurrent();
                $span = $spanBuilder
                    ->setParent($parent)
                    ->startSpan();

                $span->setStatus(StatusCode::STATUS_OK);

                $context = $span->storeInContext($parent);
                Context::storage()->attach($context);
            },
            post: static function (ConnectionInterface $client, array $params, $void, ?Throwable $exception): void {
                [$command] = $params;

                assert($command instanceof CommandInterface);

                $spanName = self::makeSpanName($command);
                $ignore = [
                    'DoctrineNamespaceCacheKey',
                    'DoctrineSecondLevelCache'
                ];

                foreach ($ignore as $value) {
                    if (str_contains($spanName, $value)) {
                        return;
                    }
                }

                $scope = Context::storage()->scope();
                if ($scope === null) {
                    return;
                }

                $scope->detach();
                $span = Span::fromContext($scope->context());
                $span->setAttribute(TraceAttributes::DEPLOYMENT_ENVIRONMENT_NAME, $_ENV['APP_ENV'] ?: 'unknown');

                if ($exception !== null) {
                    $span->recordException($exception, [
                        TraceAttributes::EXCEPTION_ESCAPED => true
                    ]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                } else {
                    $span->setStatus(StatusCode::STATUS_OK);
                }

                $span->end();
            }
        );
    }

    private static function makeSpanName(CommandInterface $command): string
    {
        if ($command instanceof AUTH || $command->getId() === 'AUTH') {
            return sprintf('%s ?', $command->getId());
        }

        $arguments = $command->getArguments();
        $firstArgument = array_shift($arguments);

        if ($firstArgument !== null) {
            return sprintf(
                '%s %s %s',
                $command->getId(),
                $firstArgument,
                implode(' ', array_map(static fn (mixed $value): string => '<?>', $arguments)),
            );
        }

        return $command->getId() !== '' ? $command->getId() : 'Unknown command';
    }

    private static function makeDbStatement(CommandInterface $command): string
    {
        if ($command instanceof AUTH || $command->getId() === 'AUTH') {
            return sprintf('%s ?', $command->getId());
        }

        return sprintf('%s %s', $command->getId(), implode(' ', $command->getArguments()));
    }
}
