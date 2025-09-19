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

namespace App\Domain\Work\Autocompleter;

use App\Application\Exception\RuntimeException;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Constant\WorkUserTypeConstant;
use Danilovl\SelectAutocompleterBundle\Model\Autocompleter\AutocompleterQuery;
use Danilovl\SelectAutocompleterBundle\Model\SelectDataFormat\Item;
use Danilovl\SelectAutocompleterBundle\Resolver\Config\AutocompleterConfigResolver;
use Danilovl\SelectAutocompleterBundle\Service\OrmAutocompleter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class WorkSearchUsersAutocompleter extends OrmAutocompleter
{
    public function __construct(
        ManagerRegistry $registry,
        AutocompleterConfigResolver $resolver,
        private readonly UserService $userService,
        private readonly UserRepository $userRepository
    ) {
        parent::__construct($registry, $resolver);
    }

    protected function createAutocompleterQueryBuilder(AutocompleterQuery $query): QueryBuilder
    {
        $user = $this->userService->getUser();

        if ($this->isUpdateConfigByResolvedFormType()) {
            $whoLooking = $this->config->route->extra['type'];
        } else {
            $whoLooking = $query->extra['type'];
        }
        /** @var string $lookingUsers */
        $lookingUsers = str_replace('work-search-', '', $this->config->name);

        $queryBuilder = match ($lookingUsers) {
            WorkUserTypeConstant::AUTHOR->value => $this->userRepository->bySearchAuthors($user, $whoLooking),
            WorkUserTypeConstant::SUPERVISOR->value => $this->userRepository->bySearchSupervisors($user, $whoLooking),
            WorkUserTypeConstant::OPPONENT->value => $this->userRepository->bySearchOpponents($user, $whoLooking),
            WorkUserTypeConstant::CONSULTANT->value => $this->userRepository->bySearchConsultants($user, $whoLooking),
            default => throw new RuntimeException('Type not found'),
        };

        if (!empty($query->search)) {
            $queryBuilder
                ->andWhere('user.firstname LIKE :name OR user.lastname LIKE :name')
                ->setParameter('name', '%' . $query->search . '%');
        }

        $queryBuilder
            ->setFirstResult($this->getOffset($query))
            ->setMaxResults($this->config->limit);

        return $queryBuilder;
    }

    /**
     * @param User&object $object
     * @return Item
     */
    public function transformObjectToItem(object $object): Item
    {
        return new Item(
            $object->getId(),
            $object->getFullNameDegree()
        );
    }
}
