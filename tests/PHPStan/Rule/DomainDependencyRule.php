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
class DomainDependencyRule implements Rule
{
    private const string DOMAIN_PREFIX = 'App\\Domain\\';

    private const array FORBIDDEN_PARTS = [
        'Facade',
        'Service',
        'Repository',
        'Factory'
    ];

    private const array ALLOWED_CLASSES = [
        'App\\Domain\\User\\Service\\UserService',
        'App\\Domain\\Work\\Factory\\WorkDetailDTOFactory',
        'App\\Domain\\Media\\Factory\\MediaDTOFactory',
        'App\\Domain\\ConversationMessage\\Factory\\WorkConversationMessageDTOFactory',
        'App\\Domain\\EventScheduleTemplate\\Factory\\EventScheduleTemplateFactory'
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

        $sourceDomain = $this->getDomain($namespace . '\\');
        if ($sourceDomain === null) {
            return [];
        }

        $targetClasses = $this->getTargetClasses($node);
        $errors = [];

        foreach ($targetClasses as $targetClass) {
            $error = $this->createError($sourceDomain, $targetClass);
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

        return [];
    }

    private function getDomain(string $class): ?string
    {
        if (!str_starts_with($class, self::DOMAIN_PREFIX)) {
            return null;
        }

        $parts = explode('\\', $class);

        return $parts[2] ?? null;
    }

    private function createError(string $sourceDomain, string $targetClass): ?IdentifierRuleError
    {
        $targetDomain = $this->getDomain($targetClass);
        if ($targetDomain === null || $targetDomain === $sourceDomain) {
            return null;
        }

        if (in_array($targetClass, self::ALLOWED_CLASSES, true)) {
            return null;
        }

        $parts = explode('\\', $targetClass);
        $targetPart = $parts[3] ?? null;
        if (!in_array($targetPart, self::FORBIDDEN_PARTS, true)) {
            return null;
        }

        $message = sprintf(
            'Domain %s must not depend on %s of domain %s (%s), use query or command bus.',
            $sourceDomain,
            $targetPart,
            $targetDomain,
            $targetClass
        );

        return RuleErrorBuilder::message($message)
            ->identifier('app.domainDependency')
            ->build();
    }
}
