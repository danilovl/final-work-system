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

namespace App\Tests\Unit\Domain\DocumentCategory\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\DocumentCategory\Security\Voter\DocumentCategoryVoter;
use App\Domain\MediaCategory\Entity\MediaCategory;
use App\Tests\Mock\Application\Security\Voter\VoterInterfaceMock;
use App\Tests\Mock\Application\Traits\VoterPublicTraitMock;
use PHPUnit\Framework\TestCase;

class DocumentCategoryVoterTest extends TestCase
{
    use VoterPublicTraitMock;

    private VoterInterfaceMock $documentCategoryVoter;

    protected function setUp(): void
    {
        $this->documentCategoryVoter = $this->createVoterPublic(DocumentCategoryVoter::class);
    }

    public function testSupports(): void
    {
        $mediaCategory = $this->createStub(MediaCategory::class);

        foreach (DocumentCategoryVoter::SUPPORTS as $support) {
            $this->assertTrue($this->documentCategoryVoter->supportsPublic($support, $mediaCategory));
        }

        $this->assertFalse($this->documentCategoryVoter->supportsPublic('invalid_attribute', $mediaCategory));
        $this->assertFalse($this->documentCategoryVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
