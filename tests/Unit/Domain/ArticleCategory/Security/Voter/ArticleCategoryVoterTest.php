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

namespace App\Tests\Unit\Domain\ArticleCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\ArticleCategory\Entity\ArticleCategory;
use App\Domain\ArticleCategory\Security\Voter\ArticleCategoryVoter;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use App\Tests\Mock\Application\Traits\VoterPublicTraitMock;
use PHPUnit\Framework\TestCase;

class ArticleCategoryVoterTest extends TestCase
{
    use VoterPublicTraitMock;

    private VoterInterfaceMock $articleCategoryVoter;

    protected function setUp(): void
    {
        $this->articleCategoryVoter = $this->createVoterPublic(ArticleCategoryVoter::class);
    }

    public function testSupports(): void
    {
        $articleCategory = $this->createStub(ArticleCategory::class);

        foreach (ArticleCategoryVoter::SUPPORTS as $support) {
            $this->assertTrue($this->articleCategoryVoter->supportsPublic($support, $articleCategory));
        }

        $this->assertFalse($this->articleCategoryVoter->supportsPublic('invalid_attribute', $articleCategory));
        $this->assertFalse($this->articleCategoryVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
