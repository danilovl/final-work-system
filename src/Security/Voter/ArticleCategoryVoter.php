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

namespace App\Security\Voter;

use App\Constant\VoterSupportConstant;
use App\Entity\ArticleCategory;
use App\Helper\FunctionHelper;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticleCategoryVoter extends Voter
{
    private const SUPPORTS = [
        VoterSupportConstant::VIEW
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof ArticleCategory) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === VoterSupportConstant::VIEW) {
            return $this->canView($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(ArticleCategory $articleCategory, User $user): bool
    {
        return $articleCategory->isActive() &&
            FunctionHelper::checkIntersectTwoArray($user->getRoles(), $articleCategory->getAccess());
    }
}