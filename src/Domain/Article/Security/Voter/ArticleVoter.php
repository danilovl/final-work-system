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

namespace App\Domain\Article\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Application\Helper\FunctionHelper;
use App\Domain\Article\Security\Voter\Subject\ArticleVoterSubject;
use App\Domain\User\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const array SUPPORTS = [
        VoterSupportConstant::VIEW->value
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::SUPPORTS, true)) {
            return false;
        }

        if (!$subject instanceof ArticleVoterSubject) {
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

        if (!$subject instanceof ArticleVoterSubject) {
            return false;
        }

        if ($attribute === VoterSupportConstant::VIEW->value) {
            return $this->canView($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function canView(ArticleVoterSubject $articleVoterSubject, User $user): bool
    {
        $article = $articleVoterSubject->article;
        $articleCategory = $articleVoterSubject->articleCategory;

        return $article->isActive() &&
            FunctionHelper::checkIntersectTwoArray($user->getRoles(), $articleCategory->getAccess()) &&
            $article->getCategories()->contains($articleCategory);
    }
}
