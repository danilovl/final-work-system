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

namespace App\Domain\ResetPassword\Repository;

use App\Domain\User\Entity\User;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use SortDirection;

class ResetPasswordQueryBuilder extends BaseQueryBuilder
{
    public function whereByToken(string $token): self
    {
        $this->queryBuilder
            ->andWhere('reset_password.hashedToken = :token')
            ->setParameter('token', $token);

        return $this;
    }

    public function whereByUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('reset_password.user = :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function orderByCreatedAt(SortDirection $order = SortDirection::Descending): self
    {
        $this->queryBuilder
            ->orderBy('reset_password.createdAt', $order)
            ->addOrderBy('reset_password.id', $order);

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryBuilder->setMaxResults($limit);

        return $this;
    }
}
