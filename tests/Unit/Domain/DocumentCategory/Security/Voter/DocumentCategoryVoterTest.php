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

namespace App\Tests\Unit\Domain\DocumentCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\DocumentCategory\Security\Voter\DocumentCategoryVoter;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class DocumentCategoryVoterTest extends TestCase
{
    use VoterPublicTrait;

    private readonly DocumentCategoryVoter $documentCategoryVoter;

    protected function setUp(): void
    {
        $this->documentCategoryVoter = $this->createVoterPublic(DocumentCategoryVoter::class);
    }

    public function testSupports(): void
    {
        $mediaCategory = $this->createMock(MediaCategory::class);

        $supports = [
            VoterSupportConstant::EDIT->value,
            VoterSupportConstant::DELETE->value
        ];

        foreach ($supports as $support) {
            $this->assertTrue($this->documentCategoryVoter->supportsPublic($support, $mediaCategory));
        }

        $this->assertFalse($this->documentCategoryVoter->supportsPublic('invalid_attribute', $mediaCategory));
        $this->assertFalse($this->documentCategoryVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
