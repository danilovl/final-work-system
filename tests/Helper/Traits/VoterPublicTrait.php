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

namespace App\Tests\Helper\Traits;

use App\Tests\Helper\Application\Security\Voter\TestVoterInterface;

trait VoterPublicTrait
{
    public function createVoterPublic(string $parentClass): TestVoterInterface
    {
        $classCode = 'return new class extends %s implements %s {
                public function supportsPublic(string $attribute, mixed $subject): bool
                {
                    return $this->supports($attribute, $subject);
                }
            };
        ';

        $classCode = sprintf($classCode, $parentClass, TestVoterInterface::class);

        return eval($classCode);
    }
}
