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

namespace App\Tests\Mock\Unit\Infrastructure\Event\EventListener;

use App\Infrastructure\Attribute\EntityRelationValidatorAttribute;

class MockControllerMethod
{
    #[EntityRelationValidatorAttribute(MockSourceEntity::class, MockTargetEntity::class)]
    public function testMethod(MockSourceEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockSourceEntity::class, MockTargetEmptyEntity::class)]
    public function testMethodNoId(MockSourceEntity $source, MockTargetEmptyEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockMultiRelationEntity::class, MockTargetEntity::class)]
    public function testMethodSeveralProperties(MockMultiRelationEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockMultiRelationEntity::class, MockTargetEntity::class, property: 'second')]
    public function testMethodSecondProperty(MockMultiRelationEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockMultiRelationEntity::class, MockTargetEntity::class, property: 'targets')]
    public function testMethodCollection(MockMultiRelationEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockMultiRelationEntity::class, MockTargetEntity::class, property: 'uninitialized')]
    public function testMethodUninitialized(MockMultiRelationEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockMultiRelationEntity::class, MockTargetEntity::class, property: 'unknown')]
    public function testMethodUnknownProperty(MockMultiRelationEntity $source, MockTargetEntity $target): void {}

    #[EntityRelationValidatorAttribute(MockTargetEmptyEntity::class, MockTargetEntity::class)]
    public function testMethodNoPropertyOfType(MockTargetEmptyEntity $source, MockTargetEntity $target): void {}

    public function testEmptyMethod(): void {}
}
