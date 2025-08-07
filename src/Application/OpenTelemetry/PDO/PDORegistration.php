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

namespace App\Application\OpenTelemetry\PDO;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use Closure;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\SemConv\{
    TraceAttributes,
    TraceAttributeValues
};
use PDO;
use PDOStatement;
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

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 80])]
class PDORegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        $instrumentation = new CachedInstrumentation(__CLASS__);
        $databaseParams = new stdClass;

        self::hookPDO($instrumentation, $databaseParams);
        self::hookPDOStatement($instrumentation, $databaseParams);
    }

    private static function hookPDO(CachedInstrumentation $instrumentation, stdClass $databaseParams): void
    {
        hook(
            PDO::class,
            '__construct',
            pre: static function (PDO $pdo, array $params) use ($databaseParams): void {
                $databaseUrl = $params[0] ?? null;

                if (is_string($databaseUrl)) {
                    preg_match('~dbname=([a-zA-Z0-9]+);~', $databaseUrl, $matches);
                    $databaseParams->dbname = $matches[1] ?? null;

                    preg_match('~host=([a-zA-Z0-9]+);~', $databaseUrl, $matches);
                    $databaseParams->host = $matches[1] ?? null;
                }

                $databaseParams->username = $params[1] ?? null;
            }
        );

        hook(
            PDO::class,
            'exec',
            pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                $sql = $params[0];
                assert(is_string($sql));

                self::startSpan($instrumentation, $sql, $class, $function, $databaseParams);
            },
            post: self::post()
        );

        hook(
            PDO::class,
            'query',
            pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                /* @var string $params */
                $sql = $params[0];

                self::startSpan($instrumentation, $sql, $class, $function, $databaseParams);
            },
            post: self::post()
        );

        hook(
            PDO::class,
            'beginTransaction',
            pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                self::startSpan($instrumentation, 'PDO->beginTransaction', $class, $function, $databaseParams);
            },
            post: self::post()
        );

        hook(
            PDO::class,
            'commit',
            pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                self::startSpan($instrumentation, 'PDO->commit', $class, $function, $databaseParams);
            },
            post: self::post()
        );

        hook(
            PDO::class,
            'rollBack',
            pre: static function (PDO $pdo, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                self::startSpan($instrumentation, 'PDO->rollBack', $class, $function, $databaseParams);
            },
            post: self::post()
        );
    }

    private static function hookPDOStatement(CachedInstrumentation $instrumentation, stdClass $databaseParams): void
    {
        hook(
            PDOStatement::class,
            'execute',
            pre: static function (PDOStatement $statement, array $params, string $class, string $function) use ($instrumentation, $databaseParams): void {
                self::startSpan($instrumentation, $statement->queryString, $class, $function, $databaseParams);
            },
            post: self::post()
        );
    }

    private static function startSpan(
        CachedInstrumentation $instrumentation,
        string $sql,
        string $class,
        string $function,
        stdClass $databaseParams,
    ): void {
        if ($sql === '') {
            $sql = 'Empty sql';
        }

        $spanName = self::simplifySql($sql);
        $spanName = sprintf('MYSQL %s', $spanName);

        $spanBuilder = $instrumentation->tracer()
            ->spanBuilder($spanName)
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
            ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
            ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_MYSQL)
            ->setAttribute(TraceAttributes::DB_QUERY_TEXT, $sql)
            ->setAttribute(TraceAttributes::DB_OPERATION_NAME, strtok($sql, ' '))
            ->setAttribute(TraceAttributes::SERVER_ADDRESS, $databaseParams->host)
            ->setAttribute(TraceAttributes::DB_NAMESPACE, $databaseParams->dbname)
            ->setAttribute('db.user', $databaseParams->username);

        $span = $spanBuilder->startSpan();

        $context = $span->storeInContext(Context::getCurrent());
        Context::storage()->attach($context);
    }

    private static function post(): Closure
    {
        return static function ($statement, $params, $result, ?Throwable $exception): void {
            $scope = Context::storage()->scope();
            if ($scope === null) {
                return;
            }

            $scope->detach();
            $span = Span::fromContext($scope->context());

            if ($exception !== null) {
                $span->recordException($exception, [
                    TraceAttributes::EXCEPTION_ESCAPED => true
                ]);
                $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
            } else {
                $span->setStatus(StatusCode::STATUS_OK);
            }

            $span->end();
        };
    }

    private static function simplifySql(string $sql): string
    {
        $sql = preg_replace('~\s+~', ' ', trim($sql));

        if (preg_match('~^(SELECT).*?FROM\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
            return mb_strtolower($matches[1]) . ' FROM ' . $matches[2];
        } elseif (preg_match('~^(UPDATE)\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
            return mb_strtolower($matches[1]) . ' ' . $matches[2];
        } elseif (preg_match('~^(INSERT INTO)\s+([a-zA-Z0-9_.]+)~i', $sql, $matches)) {
            return 'INSERT ' . mb_strtolower($matches[2]);
        }

        return $sql;
    }
}
