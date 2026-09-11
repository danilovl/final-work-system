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

namespace App\Domain\Media\Repository;

use App\Domain\Media\DTO\Repository\MediaRepositoryDTO;
use App\Domain\Media\Entity\Media;
use App\Domain\MediaType\Entity\MediaType;
use App\Domain\User\Entity\User;
use App\Domain\Work\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    private function createMediaBuilder(): MediaQueryBuilder
    {
        return new MediaQueryBuilder($this->createQueryBuilder('media'));
    }

    public function baseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('media')
            ->leftJoin('media.mimeType', 'mime_type')
            ->leftJoin('media.categories', 'categories');
    }

    public function mediaListByUserFilter(MediaRepositoryDTO $mediaData): QueryBuilder
    {
        $builder = $this->createMediaBuilder()
            ->joinMimeType()->selectMimeType()
            ->joinCategories()->selectCategories()
            ->orderByCreatedAt();

        if ($mediaData->users !== null) {
            $builder = $builder->byUsers($mediaData->users);
        }

        if ($mediaData->criteria !== null) {
            foreach ($mediaData->criteria as $field => $value) {
                if ($field === 'name' && !empty($value)) {
                    $builder = $builder->byNameLike((string) $value);
                }

                if ($field === 'categories' && !empty($value)) {
                    $builder = $builder->byCategoriesIds($value);
                }

                if ($field === 'mimeType' && !empty($value)) {
                    $builder = $builder->byMimeTypeIds($value);
                }
            }
        }

        if ($mediaData->type !== null) {
            $builder = $builder->byType($mediaData->type);
        }

        if ($mediaData->active) {
            $builder = $builder->byActive($mediaData->active);
        }

        return $builder->getQueryBuilder();
    }

    public function allByWork(Work $work): QueryBuilder
    {
        return $this->createMediaBuilder()
            ->byWork($work)
            ->orderByCreatedAt()
            ->getQueryBuilder();
    }

    public function allByUser(User $user, ?MediaType $type = null): QueryBuilder
    {
        $builder = $this->createMediaBuilder()
            ->byOwner($user)
            ->orderByCreatedAt();

        if ($type !== null) {
            $builder = $builder->joinType()->selectType()->byTypeAliasEquals($type);
        }

        return $builder->getQueryBuilder();
    }

    public function allByType(MediaType $mediaType): QueryBuilder
    {
        return $this->createMediaBuilder()
            ->byType($mediaType)
            ->getQueryBuilder();
    }
}
