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

namespace App\Tests\Unit\Domain\Article\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Article\Security\Voter\ArticleVoter;
use App\Domain\Article\Security\Voter\Subject\ArticleVoterSubject;
use App\Domain\ArticleCategory\Security\Voter\ArticleCategoryVoter;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use App\Tests\Mock\Application\Traits\VoterPublicTraitMock;
use PHPUnit\Framework\TestCase;

class ArticleVoterTest extends TestCase
{
    use VoterPublicTraitMock;

    private VoterInterfaceMock $articleVoter;

    protected function setUp(): void
    {
        $this->articleVoter = $this->createVoterPublic(ArticleVoter::class);
    }

    public function testSupports(): void
    {
        $articleVoterSubject = $this->createStub(ArticleVoterSubject::class);

        foreach (ArticleCategoryVoter::SUPPORTS as $support) {
            $this->assertTrue($this->articleVoter->supportsPublic($support, $articleVoterSubject));
        }

        $this->assertFalse($this->articleVoter->supportsPublic('invalid_attribute', $articleVoterSubject));
        $this->assertFalse($this->articleVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
