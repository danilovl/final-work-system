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

namespace App\Tests\PHPStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\{
    Instanceof_,
    New_,
    StaticCall,
    StaticPropertyFetch
};
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\{
    GroupUse,
    Use_
};
use PHPStan\Analyser\Scope;
use PHPStan\Rules\{
    IdentifierRuleError,
    Rule,
    RuleErrorBuilder
};

/**
 * @implements Rule<Node>
 */
class LayerDependencyRule implements Rule
{
    private const array FORBIDDEN_DEPENDENCIES = [
        'App\\Infrastructure\\' => ['App\\Domain\\', 'App\\Application\\'],
        'App\\Application\\' => ['App\\Domain\\']
    ];

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace();
        if ($namespace === null) {
            return [];
        }

        $targetClasses = $this->getTargetClasses($node);
        $sourceNamespace = $namespace . '\\';
        $errors = [];

        foreach ($targetClasses as $targetClass) {
            $error = $this->createError($sourceNamespace, $targetClass);
            if ($error !== null) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function getTargetClasses(Node $node): array
    {
        if ($node instanceof Use_) {
            return array_map(static fn (Node\UseItem $useItem): string => $useItem->name->toString(), $node->uses);
        }

        if ($node instanceof GroupUse) {
            $prefix = $node->prefix->toString();

            return array_map(static fn (Node\UseItem $useItem): string => $prefix . '\\' . $useItem->name->toString(), $node->uses);
        }

        $name = $node;
        $isClassReference = $node instanceof Instanceof_
            || $node instanceof New_
            || $node instanceof StaticCall
            || $node instanceof StaticPropertyFetch;

        if ($isClassReference) {
            $name = $node->class;
        }

        if (!$name instanceof Name) {
            return [];
        }

        $originalName = $name->getAttribute('originalName');
        if ($originalName instanceof FullyQualified) {
            return [$name->toString()];
        }

        return [];
    }

    private function createError(string $sourceNamespace, string $targetClass): ?IdentifierRuleError
    {
        foreach (self::FORBIDDEN_DEPENDENCIES as $layer => $forbiddenLayers) {
            if (!str_starts_with($sourceNamespace, $layer)) {
                continue;
            }

            $forbiddenLayer = array_find(
                $forbiddenLayers,
                static fn (string $forbidden): bool => str_starts_with($targetClass, $forbidden)
            );

            if ($forbiddenLayer === null) {
                return null;
            }

            $message = sprintf(
                'Layer %s must not depend on %s (%s).',
                mb_rtrim($layer, '\\'),
                mb_rtrim($forbiddenLayer, '\\'),
                $targetClass
            );

            return RuleErrorBuilder::message($message)
                ->identifier('app.layerDependency')
                ->build();
        }

        return null;
    }
}
