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

namespace FinalWork\FinalWorkBundle\Helper;

use Collator;
use FinalWork\FinalWorkBundle\Constant\ConversationTypeConstant;
use FinalWork\FinalWorkBundle\Entity\{
    Conversation,
    WorkCategory
};
use FinalWork\SonataUserBundle\Entity\User;
use LogicException;

class ConversationHelper
{
    /**
     * @param iterable $conversations
     * @return array
     */
    public static function groupConversationsByCategory(iterable $conversations): array
    {
        $categoryGroup = [];

        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $work = $conversation->getWork();
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
     * @param iterable $conversations
     * @return array
     */
    public static function groupConversationsByCategorySorting(iterable $conversations): array
    {
        $categoryGroupWithSorting = [];
        $categoryGroupWithoutSorting = [];

        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $work = $conversation->getWork();

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

        return array_merge($categoryGroupWithSorting, $categoryGroupWithoutSorting);
    }

    /**
     * @param iterable $conversations
     * @param User $user
     * @throws LogicException
     */
    public static function getConversationOpposite(iterable $conversations, User $user): void
    {
        foreach ($conversations as $conversation) {
            if ($conversation->getType()->getId() === ConversationTypeConstant::WORK) {
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
    }

    /**
     * @param array $array
     */
    public static function usortCzechArray(array &$array): void
    {
        $collator = new Collator('cs_CZ.UTF-8');

        /** @var Conversation $second */
        usort($array, function ($first, $second) use ($collator) {
            /** @var Conversation $first
             * @var Conversation $second
             */
            $f = (string)$first->getRecipient();
            $s = (string)$second->getRecipient();

            return $collator->compare($f, $s);
        });
    }
}
