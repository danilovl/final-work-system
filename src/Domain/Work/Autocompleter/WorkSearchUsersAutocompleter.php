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

use App\Domain\User\Entity\User;
use App\Domain\User\Service\UserService;
use Danilovl\SelectAutocompleterBundle\Model\Autocompleter\AutocompleterQuery;
use Danilovl\SelectAutocompleterBundle\Model\SelectDataFormat\Item;
use Danilovl\SelectAutocompleterBundle\Resolver\Config\AutocompleterConfigResolver;
use Danilovl\SelectAutocompleterBundle\Service\OrmAutocompleter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Override;
use App\Application\Interfaces\Bus\QueryBusInterface;
use App\Domain\User\Bus\Query\UserSearchQueryBuilder\{GetUserSearchQueryBuilderQuery, GetUserSearchQueryBuilderQueryResult};

class WorkSearchUsersAutocompleter extends OrmAutocompleter
{
    public function __construct(
        ManagerRegistry $registry,
        AutocompleterConfigResolver $resolver,
        private readonly UserService $userService,
        private readonly QueryBusInterface $queryBus
    ) {
        parent::__construct($registry, $resolver);
    }

    #[Override]
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

        $getUserSearchQueryBuilderQuery = GetUserSearchQueryBuilderQuery::create(
            user: $user,
            lookingUsers: $lookingUsers,
            whoLooking: $whoLooking
        );
        /** @var GetUserSearchQueryBuilderQueryResult $getUserSearchQueryBuilderQueryResult */
        $getUserSearchQueryBuilderQueryResult = $this->queryBus->handle($getUserSearchQueryBuilderQuery);
        $queryBuilder = $getUserSearchQueryBuilderQueryResult->queryBuilder;

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
    #[Override]
    public function transformObjectToItem(object $object): Item
    {
        return new Item(
            $object->getId(),
            $object->getFullNameDegree()
        );
    }
}
