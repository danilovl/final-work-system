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

namespace App\Domain\Conversation\Helper;

use App\Domain\Conversation\Entity\Conversation;
use App\Domain\ConversationType\Constant\ConversationTypeConstant;
use App\Domain\User\Entity\User;
use App\Domain\WorkCategory\Entity\WorkCategory;
use Collator;
use LogicException;

class ConversationHelper
{
    /**
     * @param Conversation[] $conversations
     */
    public static function groupConversationsByCategory(iterable $conversations): array
    {
        $categoryGroup = [];

        foreach ($conversations as $conversation) {
            $work = $conversation->getWork();
            if ($work === null) {
                continue;
            }

            $categories = $work->getCategories();

            if ($categories->count() > 0) {
                /** @var WorkCategory $category */
                foreach ($categories as $category) {
                    if (isset($categoryGroup[$category->getName()]) === false) {
                        $categoryGroup[$category->getName()] = [];
                    }
                    $categoryGroup[$category->getName()][] = $conversation;
                }
            } else {
                if (isset($categoryGroup['-']) === false) {
                    $categoryGroup['-'] = [];
                }
                $categoryGroup['-'][] = $conversation;
            }
        }

        ksort($categoryGroup);

        return $categoryGroup;
    }

    /**
     * @return array<string, array<Conversation>>
     */
    public static function groupConversationsByCategorySorting(iterable $conversations): array
    {
        $categoryGroupWithSorting = [];
        $categoryGroupWithoutSorting = [];

        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $work = $conversation->getWork();
            if ($work === null) {
                continue;
            }

            $categories = $work->getCategories();
            if ($categories->count() > 0) {
                /** @var WorkCategory $category */
                foreach ($categories as $category) {
                    $categoryName = $category->getName();
                    if ($category->getSorting()) {
                        $categoryName = sprintf('%s: %s', $category->getSorting(), $categoryName);
                        if (isset($categoryGroupWithSorting[$categoryName]) === false) {
                            $categoryGroupWithSorting[$categoryName] = [];
                        }
                        $categoryGroupWithSorting[$categoryName][] = $conversation;
                    } else {
                        if (isset($categoryGroupWithoutSorting[$categoryName]) === false) {
                            $categoryGroupWithoutSorting[$categoryName] = [];
                        }
                        $categoryGroupWithoutSorting[$categoryName][] = $conversation;
                    }
                }
            } else {
                if (isset($categoryGroupWithoutSorting['-']) === false) {
                    $categoryGroupWithoutSorting['-'] = [];
                }
                $categoryGroupWithoutSorting['-'][] = $conversation;
            }
        }

        ksort($categoryGroupWithSorting);
        ksort($categoryGroupWithoutSorting);

        /** @var array<string, array<Conversation>> $result */
        $result = array_merge($categoryGroupWithSorting, $categoryGroupWithoutSorting);

        return $result;
    }

    public static function getConversationOpposite(iterable $conversations, User $user): void
    {
        foreach ($conversations as $conversation) {
            if ($conversation->getType()->getId() !== ConversationTypeConstant::WORK->value) {
                continue;
            }

            /** @var Conversation $conversation */
            $participants = $conversation->getParticipants();
            if ($participants->count() > 2) {
                throw new LogicException('Conversation must have only two user');
            }

            foreach ($participants as $participant) {
                if ($participant->getUser()->getId() !== $user->getId()) {
                    $conversation->setRecipient($participant->getUser());
                }
            }
        }
    }

    /**
     * @param Conversation[] $array
     */
    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        usort($array, static function (Conversation $first, Conversation $second) use ($collator): int {
            $f = (string) $first->getRecipient();
            $s = (string) $second->getRecipient();

            /** @var int $compare */
            $compare = $collator->compare($f, $s);

            return $compare;
        });
    }

    /**
     * @return int[]
     */
    public static function getParticipantIds(Conversation $conversation): array
    {
        $participantIds = [];
        $participants = $conversation->getParticipants();

        foreach ($participants as $participant) {
            $participantIds[] = $participant->getUser()->getId();
        }

        sort($participantIds);

        return $participantIds;
    }
}
