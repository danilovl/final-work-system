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

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

trait VoterPublicTrait
{
    public function createVoterPublic(string $parentClass): VoterInterface
    {
        $classCode = 'return new class extends %s {
                public function supportsPublic(string $attribute, mixed $subject): bool
                {
                    return $this->supports($attribute, $subject);
                }
            };
        ';

        $classCode = sprintf($classCode, $parentClass);

        return eval($classCode);
    }
}
