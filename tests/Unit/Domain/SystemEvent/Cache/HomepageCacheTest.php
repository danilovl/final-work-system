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

namespace App\Tests\Unit\Domain\SystemEvent\Cache;

use App\Domain\SystemEvent\Cache\HomepageCache;
use App\Domain\SystemEvent\Facade\SystemEventRecipientFacade;
use App\Domain\User\Entity\User;
use App\Infrastructure\Service\PaginatorService;
use Danilovl\ParameterBundle\Interfaces\ParameterServiceInterface;
use Doctrine\ORM\{
    Configuration,
    EntityManagerInterface,
    Query
};
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class HomepageCacheTest extends TestCase
{
    private HomepageCache $homepageCache;

    protected function setUp(): void
    {
        $cache = new ArrayAdapter;

        $parameterService = $this->createStub(ParameterServiceInterface::class);
        $parameterService
            ->method('getInt')
            ->willReturn(1);

        $slidingPagination = new SlidingPagination([]);

        $paginatorService = $this->createStub(PaginatorService::class);
        $paginatorService
            ->method('createPagination')
            ->willReturn($slidingPagination);

        $configuration = $this->createStub(Configuration::class);
        $configuration
            ->method('getDefaultQueryHints')
            ->willReturn([]);

        $configuration
            ->method('isSecondLevelCacheEnabled')
            ->willReturn(false);

        $entityManagerInterface = $this->createStub(EntityManagerInterface::class);
        $entityManagerInterface
            ->method('getConfiguration')
            ->willReturn($configuration);

        $systemEventRecipientFacade = $this->createStub(SystemEventRecipientFacade::class);
        $systemEventRecipientFacade
            ->method('queryRecipientsQueryByUser')
            ->willReturn(new Query($entityManagerInterface));

        $this->homepageCache = new HomepageCache(
            cache: $cache,
            parameterService: $parameterService,
            paginatorService: $paginatorService,
            systemEventRecipientFacade: $systemEventRecipientFacade
        );
    }

    public function testCreateHomepagePaginator(): void
    {
        $user = new User;
        $user->setId(1);

        $result = $this->homepageCache->createHomepagePaginator($user);

        $this->assertCount(1, $result);
    }
}
