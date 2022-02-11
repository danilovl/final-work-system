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

namespace App\Application\Helper;

use App\Application\Constant\DateFormatConstant;
use App\Domain\Work\Entity\Work;
use App\Domain\WorkCategory\Entity\WorkCategory;

class WorkFunctionHelper
{
    public static function groupWorksByDeadline(iterable $works): array
    {
        $deadlineGroup = [];

        /** @var Work $work */
        foreach ($works as $work) {
            $deadline = $work->getDeadline()->format(DateFormatConstant::DATE);

            if (isset($deadlineGroup[$deadline])) {
                $deadlineGroup[$deadline]['works'][] = $work;
            } else {
                $deadlineGroup[$deadline] = [];
                $deadlineGroup[$deadline]['works'] = [];
                $deadlineGroup[$deadline]['works'][] = $work;
            }
        }

        return $deadlineGroup;
    }

    public static function groupWorksByCategory(iterable $works): array
    {
        $categoryGroup = [];

        /** @var Work $work */
        foreach ($works as $work) {
            $categories = $work->getCategories();

            if ($categories->count() > 0) {
                /** @var WorkCategory $category */
                foreach ($categories as $category) {
                    if (!isset($categoryGroup[$category->getName()])) {
                        $categoryGroup[$category->getName()] = [];
                        $categoryGroup[$category->getName()]['works'] = [];
                    }

                    $categoryGroup[$category->getName()]['works'][] = $work;
                }
            } else {
                if (!isset($categoryGroup['-'])) {
                    $categoryGroup['-'] = [];
                    $categoryGroup['-']['works'] = [];
                }

                $categoryGroup['-']['works'][] = $work;
            }
        }

        return $categoryGroup;
    }

    public static function groupWorksByCategoryAndSorting(iterable $works): array
    {
        $categoryGroupWithoutSorting = [];
        $categoryGroupWithSorting = [];

        /** @var Work $work */
        foreach ($works as $work) {
            $categories = $work->getCategories();

            if ($categories->count() > 0) {
                /** @var WorkCategory $category */
                foreach ($categories as $category) {

                    $categoryName = $category->getName();
                    if ($category->getSorting()) {
                        $categoryName = $category->getSorting() . ': ' . $categoryName;
                        if (!isset($categoryGroupWithSorting[$categoryName])) {
                            $categoryGroupWithSorting[$categoryName] = [];
                            $categoryGroupWithSorting[$categoryName]['id'] = $category->getId();
                            $categoryGroupWithSorting[$categoryName]['works'] = [];
                        }

                        $categoryGroupWithSorting[$categoryName]['works'][] = $work;
                    } else {
                        if (!isset($categoryGroupWithoutSorting[$categoryName])) {
                            $categoryGroupWithoutSorting[$categoryName] = [];
                            $categoryGroupWithoutSorting[$categoryName]['works'] = [];
                            $categoryGroupWithoutSorting[$categoryName]['id'] = $category->getId();
                        }

                        $categoryGroupWithoutSorting[$categoryName]['works'][] = $work;
                    }
                }
            } else {
                if (!isset($categoryGroupWithoutSorting['-'])) {
                    $categoryGroupWithoutSorting['-'] = [];
                    $categoryGroupWithoutSorting['-']['works'] = [];
                }

                $categoryGroupWithoutSorting['-']['works'][] = $work;
            }
        }

        ksort($categoryGroupWithSorting);
        ksort($categoryGroupWithoutSorting);

        return array_merge($categoryGroupWithSorting, $categoryGroupWithoutSorting);
    }
}
