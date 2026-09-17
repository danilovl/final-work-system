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

namespace App\Domain\User\Repository;

use App\Application\Exception\RuntimeException;
use App\Domain\User\Entity\User;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use App\Domain\WorkStatus\Entity\WorkStatus;
use App\Infrastructure\Persistence\Doctrine\Repository\BaseQueryBuilder;
use Doctrine\Common\Collections\Order;

class UserQueryBuilder extends BaseQueryBuilder
{
    public function filterEnabled(?bool $enable): self
    {
        if ($enable !== null) {
            $this->queryBuilder
                ->andWhere('user.enabled = :enable')
                ->setParameter('enable', $enable);
        }

        return $this;
    }

    public function orderByName(): self
    {
        $this->queryBuilder
            ->addOrderBy('user.lastname', Order::Ascending->value)
            ->addOrderBy('user.firstname', Order::Ascending->value);

        return $this;
    }

    public function filterRoleLike(string $role): self
    {
        $this->queryBuilder
            ->andWhere('user.roles LIKE :roles')
            ->setParameter('roles', "%$role%");

        return $this;
    }

    public function joinGroups(): self
    {
        $this->queryBuilder
            ->addSelect('groups')
            ->leftJoin('user.groups', 'groups');

        return $this;
    }

    public function leftJoinWorks(): self
    {
        $this->queryBuilder
            ->leftJoin('user.authorWorks', 'author_work')
            ->leftJoin('user.opponentWorks', 'opponent_work')
            ->leftJoin('user.consultantWorks', 'consultant_work');

        return $this;
    }

    public function filterWithoutAnyWorks(): self
    {
        $this->queryBuilder
            ->andWhere('author_work IS NULL')
            ->andWhere('opponent_work IS NULL')
            ->andWhere('consultant_work IS NULL');

        return $this;
    }

    public function filterNotUser(User $user): self
    {
        $this->queryBuilder
            ->andWhere('user != :user')
            ->setParameter('user', $user);

        return $this;
    }

    public function filterRoleLikeAny(array $roles): self
    {
        if ($roles === []) {
            return $this;
        }

        $expr = $this->queryBuilder->expr();
        $orX = $expr->orX();

        foreach ($roles as $role) {
            $param = sprintf('role_%d', $role);
            $orX->add($expr->like('user.roles', ':' . $param));
            $this->queryBuilder->setParameter($param, "%$role%");
        }

        $this->queryBuilder->andWhere($orX);

        return $this;
    }

    public function bySupervisor(
        User $user,
        string $type,
        iterable|WorkStatus|null $workStatus = null
    ): self {
        $this->queryBuilder
            ->addSelect('work, groups')
            ->leftJoin('user.groups', 'groups');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->queryBuilder
                    ->join('user.authorWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->queryBuilder
                    ->join('user.opponentWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->queryBuilder
                    ->join('user.consultantWorks', 'work')
                    ->where('work.supervisor = :supervisor');

                break;
        }

        $this->queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('supervisor', $user);

        if ($workStatus instanceof WorkStatus) {
            $this->queryBuilder
                ->andWhere('work.status = :status')
                ->setParameter('status', $workStatus);
        } elseif (is_iterable($workStatus)) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work.status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        return $this;
    }

    public function bySearchAuthors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): self {
        $this->queryBuilder
            ->join('user.authorWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::SUPERVISOR->value:
                $this->queryBuilder->where('work.supervisor = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work.status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        $this->queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $this;
    }

    public function bySearchSupervisors(
        User $user,
        string $type,
        ?array $workStatus = null
    ): self {
        $this->queryBuilder
            ->join('user.supervisorWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work.status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        $this->queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $this;
    }

    public function bySearchOpponents(
        User $user,
        string $type,
        ?array $workStatus = null
    ): self {
        $this->queryBuilder
            ->join('user.opponentWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $this->queryBuilder->where('work.supervisor = :user');

                break;
            case WorkUserTypeConstant::CONSULTANT->value:
                $this->queryBuilder->where('work.consultant = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work.status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        $this->queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $this;
    }

    public function bySearchConsultants(
        User $user,
        string $type,
        ?array $workStatus = null
    ): self {
        $this->queryBuilder
            ->join('user.consultantWorks', 'work');

        switch ($type) {
            case WorkUserTypeConstant::AUTHOR->value:
                $this->queryBuilder->where('work.author = :user');

                break;
            case WorkUserTypeConstant::OPPONENT->value:
                $this->queryBuilder->where('work.opponent = :user');

                break;
            case WorkUserTypeConstant::SUPERVISOR->value:
                $this->queryBuilder->where('work.supervisor = :user');

                break;
            default:
                throw new RuntimeException('Type not found');
        }

        if ($workStatus !== null) {
            $this->queryBuilder
                ->andWhere($this->queryBuilder->expr()->in('work.status', ':statuses'))
                ->setParameter('statuses', $workStatus);
        }

        $this->queryBuilder
            ->orderBy('user.lastname', Order::Ascending->value)
            ->groupBy('user.id')
            ->setParameter('user', $user);

        return $this;
    }

    public function byUsername(string $username): self
    {
        $this->queryBuilder
            ->andWhere('user.username = :username')
            ->setParameter('username', $username);

        return $this;
    }

    public function byToken(string $token): self
    {
        $this->queryBuilder
            ->andWhere('user.token = :token')
            ->setParameter('token', $token);

        return $this;
    }

    public function byEmail(string $email): self
    {
        $this->queryBuilder
            ->andWhere('user.email = :email')
            ->setParameter('email', $email);

        return $this;
    }
}
