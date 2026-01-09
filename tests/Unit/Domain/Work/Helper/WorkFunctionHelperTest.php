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

namespace App\Tests\Unit\Domain\Work\Helper;

use App\Domain\Work\Entity\Work;
use App\Domain\Work\Helper\WorkFunctionHelper;
use App\Domain\WorkCategory\Entity\WorkCategory;
use DateTime;
use PHPUnit\Framework\TestCase;

class WorkFunctionHelperTest extends TestCase
{
    public function testGroupWorksByDeadline(): void
    {
        $works = [];
        $expectedResult = [];

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));
        $works[] = $work;
        $expectedResult['2023-01-01']['works'][] = $work;

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));
        $works[] = $work;
        $expectedResult['2023-01-01']['works'][] = $work;

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-02'));
        $works[] = $work;
        $expectedResult['2023-01-02']['works'][] = $work;

        $result = WorkFunctionHelper::groupWorksByDeadline($works);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGroupWorksByCategory(): void
    {
        $expectedResult = [];
        $works = [];

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));

        $category = new WorkCategory;
        $category->setId(1);
        $category->setName('First');
        $category->setSorting('A');
        $work->getCategories()->add($category);

        $expectedResult['First']['works'][] = $work;

        $category = new WorkCategory;
        $category->setId(2);
        $category->setName('Second');
        $category->setSorting('B');
        $work->getCategories()->add($category);

        $expectedResult['Second']['works'][] = $work;

        $works[] = $work;

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));
        $works[] = $work;
        $expectedResult['-']['works'][] = $work;

        $result = WorkFunctionHelper::groupWorksByCategory($works);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGroupWorksByCategoryAndSorting(): void
    {
        $expectedResult = [];
        $works = [];

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));

        $category = new WorkCategory;
        $category->setId(1);
        $category->setName('First');
        $category->setSorting('A');
        $work->getCategories()->add($category);

        $expectedResult['A: First']['id'] = $category->getId();
        $expectedResult['A: First']['works'][] = $work;

        $category = new WorkCategory;
        $category->setId(2);
        $category->setName('Second');
        $category->setSorting('B');
        $work->getCategories()->add($category);

        $expectedResult['B: Second']['id'] = $category->getId();
        $expectedResult['B: Second']['works'][] = $work;

        $works[] = $work;

        $work = new Work;
        $work->setDeadline(new DateTime('2023-01-01'));
        $works[] = $work;
        $expectedResult['-']['works'][] = $work;

        $result = WorkFunctionHelper::groupWorksByCategoryAndSorting($works);

        $this->assertEquals($expectedResult, $result);
    }
}
