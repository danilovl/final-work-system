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

namespace App\Domain\ConversationType\Facade;

use App\Domain\ConversationType\Entity\ConversationType;
use App\Domain\ConversationType\Repository\ConversationTypeRepository;
use Webmozart\Assert\Assert;

readonly class ConversationTypeFacade
{
    public function __construct(private ConversationTypeRepository $conversationTypeRepository) {}

    /**
     * @return ConversationType[]
     */
    public function getAll(): array
    {
        /** @var array $result */
        $result = $this->conversationTypeRepository->findAll();

        Assert::allIsInstanceOf($result, ConversationType::class);

        return $result;
    }
}
