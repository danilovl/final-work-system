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

namespace App\Application\OpenTelemetry\Elastica;

use App\Application\OpenTelemetry\OpenTelemetryRegistrationInterface;
use Elastica\Exception\ExceptionInterface;
use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Endpoints\AbstractEndpoint;
use FOS\ElasticaBundle\Elastica\Client as FOSElastica;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use OpenTelemetry\SemConv\{
    TraceAttributes,
    TraceAttributeValues
};
use ReflectionClass;
use OpenTelemetry\API\Trace\{
    Span,
    SpanKind,
    StatusCode
};
use OpenTelemetry\Context\Context;
use Throwable;
use function OpenTelemetry\Instrumentation\hook;

#[AutoconfigureTag('app.open_telemetry.registration', ['priority' => 0])]
class ElasticaRegistration implements OpenTelemetryRegistrationInterface
{
    public static function registration(): void
    {
        self::hookElasticsearchClient();
        self::hookElasticaClient();
    }

    private static function hookElasticsearchClient(): void
    {
        hook(ElasticsearchClient::class, 'performRequest', pre: self::getPreCallback(), post: self::getPostCallback());
    }

    private static function getPreCallback(): callable
    {
        return static function (ElasticsearchClient $client, array $params, string $class, string $function): void {
            $instrumentation = new CachedInstrumentation(__CLASS__);
            [$endpoint] = $params;

            assert($endpoint instanceof AbstractEndpoint);

            $endpointReflection = new ReflectionClass($endpoint::class);
            $endpointOperation = mb_strtolower($endpointReflection->getShortName());
            $endpointDocId = $endpointReflection->getProperty('id')->getValue($endpoint);

            $spanName = self::makeSpanName($endpoint);
            $spanName = sprintf('ELASTICA %s', $spanName);

            $spanBuilder = $instrumentation
                ->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_ELASTICSEARCH)
                ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $endpoint->getMethod())
                ->setAttribute(TraceAttributes::URL_FULL, $endpoint->getURI())
                ->setAttribute(TraceAttributes::DB_OPERATION_NAME, $endpointOperation)
                ->setAttribute('db.elasticsearch.path_parts.index', $endpoint->getIndex())
                ->setAttribute('db.elasticsearch.path_parts.doc_id', $endpointDocId);

            $body = $endpoint->getBody();

            if (is_array($body)) {
                try {
                    $spanBuilder->setAttribute(
                        TraceAttributes::DB_QUERY_TEXT,
                        json_encode($body, JSON_THROW_ON_ERROR)
                    );
                } catch (Throwable) {
                }
            } elseif (is_string($body)) {
                $spanBuilder->setAttribute(TraceAttributes::DB_QUERY_TEXT, $body);
            }

            $span = $spanBuilder->startSpan();
            $context = $span->storeInContext(Context::getCurrent());

            Context::storage()->attach($context);
        };
    }

    private static function getPostCallback(): callable
    {
        return static function (FOSElastica $client, array $params, $result, ?Throwable $exception): void {
            if ($exception instanceof Missing404Exception) {
                $exception = null;
            }

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

    private static function hookElasticaClient(): void
    {
        hook(FOSElastica::class, 'request', pre: self::getPreElasticaCallback(), post: self::getPostElasticaCallback());
    }

    private static function getPreElasticaCallback(): callable
    {
        return static function (FOSElastica $client, array $params, string $class, string $function): void {
            [$path, $method, $data] = $params;
            $instrumentation = new CachedInstrumentation(__CLASS__);

            $spanName = sprintf('ELASTICA %s', $path);

            $spanBuilder = $instrumentation
                ->tracer()
                ->spanBuilder($spanName)
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttribute(TraceAttributes::CODE_FUNCTION, $function)
                ->setAttribute(TraceAttributes::CODE_NAMESPACE, $class)
                ->setAttribute(TraceAttributes::DB_SYSTEM, TraceAttributeValues::DB_SYSTEM_ELASTICSEARCH)
                ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, $method)
                ->setAttribute(TraceAttributes::URL_FULL, $path);

            if (is_array($data)) {
                try {
                    $spanBuilder->setAttribute(
                        TraceAttributes::DB_QUERY_TEXT,
                        json_encode($data, JSON_THROW_ON_ERROR)
                    );
                } catch (Throwable) {
                }
            } elseif (is_string($data)) {
                $spanBuilder->setAttribute(TraceAttributes::DB_QUERY_TEXT, $data);
            }

            $span = $spanBuilder->startSpan();
            $context = $span->storeInContext(Context::getCurrent());

            Context::storage()->attach($context);
        };
    }

    private static function getPostElasticaCallback(): callable
    {
        return static function (FOSElastica $client, array $params, $result, ?Throwable $exception): void {
            if (!$exception instanceof ExceptionInterface) {
                $exception = null;
            }

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

    private static function makeSpanName(AbstractEndpoint $endpoint): string
    {
        if ($endpoint->getMethod() === '') {
            return 'Search elasticsearch';
        }

        $uri = (string) preg_replace('~/_doc/\d+~', '/_doc/{docId}', $endpoint->getURI());
        $uri = (string) preg_replace('~/scroll/[A-Za-z0-9_-]+~', '/scroll/{scrollId}', $uri);

        return sprintf('%s %s', $endpoint->getMethod(), $uri);
    }
}
