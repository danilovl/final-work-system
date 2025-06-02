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

namespace App\Domain\Work\Autocompleter;

use App\Application\Constant\DateFormatConstant;
use App\Domain\User\Service\UserService;
use App\Domain\Work\Repository\WorkRepository;
use Danilovl\SelectAutocompleterBundle\Attribute\AsAutocompleter;
use Danilovl\SelectAutocompleterBundle\Model\Autocompleter\AutocompleterQuery;
use Danilovl\SelectAutocompleterBundle\Model\SelectDataFormat\Item;
use Danilovl\SelectAutocompleterBundle\Resolver\Config\AutocompleterConfigResolver;
use Danilovl\SelectAutocompleterBundle\Service\OrmAutocompleter;
use Danilovl\SelectAutocompleterBundle\Tool\Paginator\Interfaces\PaginatorInterface;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

#[AsAutocompleter(alias: 'own.work-search-deadline')]
class WorkSearchDeadlineAutocompleter extends OrmAutocompleter
{
    public function __construct(
        ManagerRegistry $registry,
        AutocompleterConfigResolver $resolver,
        private readonly UserService $userService,
        private readonly WorkRepository $workRepository
    ) {
        parent::__construct($registry, $resolver);
    }

    public function reverseTransform(array $identifiers): array
    {
        $queryBuilder = $this->createAutocompleterQueryBuilder(new AutocompleterQuery);
        $queryBuilder->andWhere("work.deadline IN (:deadlines)")
            ->setParameter('deadlines', $identifiers);

        /** @var array<array{deadline: DateTimeInterface}> $result */
        $result = $queryBuilder->getQuery()->getResult();

        return array_map(static function (array $object): string {
            return $object['deadline']->format(DateFormatConstant::DATE->value);
        }, $result);
    }

    /**
     * @param string[] $objects
     * @return string[]
     */
    public function reverseTransformResultIds(array $objects): array
    {
        return $objects;
    }

    /**
     * @param string[] $objects
     */
    public function transformObjectsToItem(array $objects): array
    {
        return array_map(static function (string $object): Item {
            return new Item($object, $object);
        }, $objects);
    }

    protected function createPaginator(): PaginatorInterface
    {
        $queryBuilder = $this->createAutocompleterQueryBuilder($this->autocompleterQuery);

        return new readonly class ($queryBuilder) implements PaginatorInterface {
            private Paginator $paginator;

            public function __construct(QueryBuilder $queryBuilder)
            {
                $paginator = new Paginator($queryBuilder);
                $paginator->setUseOutputWalkers(false);

                $this->paginator = $paginator;
            }

            public function getTotalCount(): int
            {
                return $this->paginator->count();
            }

            public function getResult(): array
            {
                /** @var array{deadline: DateTimeInterface} $result */
                $result = $this->paginator->getQuery()->getResult();
                /** @var DateTimeInterface[] $result */
                $result = array_column($result, 'deadline');

                return array_map(static function (DateTimeInterface $object): string {
                    return $object->format(DateFormatConstant::DATE->value);
                }, $result);
            }
        };
    }

    protected function createAutocompleterQueryBuilder(AutocompleterQuery $query): QueryBuilder
    {
        $user = $this->userService->getUser();
        $queryBuilder = $this->workRepository->workDeadlineBySupervisor($user);

        $queryBuilder
            ->setFirstResult($this->getOffset($query))
            ->setMaxResults($this->config->limit);

        return $queryBuilder;
    }
}
