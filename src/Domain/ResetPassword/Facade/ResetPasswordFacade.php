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

namespace App\Domain\ResetPassword\Facade;

use App\Domain\ResetPassword\Entity\ResetPassword;
use App\Domain\ResetPassword\Repository\ResetPasswordRepository;
use App\Domain\User\Entity\User;
use DateTime;

class ResetPasswordFacade
{
    public function __construct(private ResetPasswordRepository $resetPasswordRepository)
    {
    }

    public function find(int $id): ?ResetPassword
    {
        return $this->resetPasswordRepository->find($id);
    }

    public function findResetPasswordByToken(string $token): ?ResetPassword
    {
        return $this->resetPasswordRepository
            ->byToken($token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeResetPassword(ResetPassword $resetPassword): void
    {
        $this->resetPasswordRepository
            ->baseQueryBuilder()
            ->delete()
            ->where('reset_password.user = :user')
            ->setParameter('user', $resetPassword->getUser())
            ->getQuery()
            ->execute();
    }

    public function getMostRecentNonExpiredRequestDate(User $user): ?DateTime
    {
        $resetPassword = $this->resetPasswordRepository
            ->mostRecentNonExpiredRequestDate($user)
            ->getQuery()
            ->getOneorNullResult();

        if ($resetPassword !== null && !$resetPassword->isExpired()) {
            return $resetPassword->getCreatedAt();
        }

        return null;
    }
}
