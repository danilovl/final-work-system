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

namespace App\Application\OpenTelemetry\Twig;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\{
    SpanKind,
    SpanInterface
};
use OpenTelemetry\Context\Context;
use SplObjectStorage;
use Twig\Extension\AbstractExtension;
use Twig\Profiler\NodeVisitor\ProfilerNodeVisitor;
use Twig\Profiler\Profile;

class TraceableTwigExtension extends AbstractExtension
{
    /**
     * @var SplObjectStorage<Profile, SpanInterface>
     */
    private SplObjectStorage $spans;

    public function __construct()
    {
        $this->spans = new SplObjectStorage;
    }

    public function enter(Profile $profile): void
    {
        $scope = Context::storage()->scope();

        $spanName = sprintf('TWIG %s', $this->getSpanName($profile));

        $spanBuilder = Globals::tracerProvider()
            ->getTracer(__CLASS__)
            ->spanBuilder($spanName)
            ->setSpanKind(SpanKind::KIND_INTERNAL)
            ->setParent($scope?->context());

        $span = $spanBuilder->startSpan();

        $this->spans[$profile] = $span;
    }

    public function leave(Profile $profile): void
    {
        if (!isset($this->spans[$profile])) {
            return;
        }

        $span = $this->spans[$profile];
        $span->end();

        unset($this->spans[$profile]);
    }

    public function getNodeVisitors(): array
    {
        return [
            new ProfilerNodeVisitor(self::class)
        ];
    }

    private function getSpanName(Profile $profile): string
    {
        return match (true) {
            $profile->isRoot() => $profile->getName(),
            $profile->isTemplate() => $profile->getTemplate(),
            default => sprintf('%s::%s(%s)', $profile->getTemplate(), $profile->getType(), $profile->getName())
        };
    }
}
