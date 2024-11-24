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

namespace App\Tests\Unit\Domain\Document\Security\Voter;

use App\Application\Constant\VoterSupportConstant;
use App\Domain\Document\Security\Voter\DocumentVoter;
use App\Domain\Media\Entity\Media;
use App\Tests\Helper\Traits\VoterPublicTrait;
use PHPUnit\Framework\TestCase;

class DocumentVoterTest extends TestCase
{
    use VoterPublicTrait;

    private DocumentVoter $documentVoter;

    protected function setUp(): void
    {
        $this->documentVoter = $this->createVoterPublic(DocumentVoter::class);
    }

    public function testSupports(): void
    {
        $media = $this->createMock(Media::class);

        foreach (DocumentVoter::SUPPORTS as $support) {
            $this->assertTrue($this->documentVoter->supportsPublic($support, $media));
        }

        $this->assertFalse($this->documentVoter->supportsPublic('invalid_attribute', $media));
        $this->assertFalse($this->documentVoter->supportsPublic(VoterSupportConstant::VIEW->value, 'invalid_subject'));
    }
}
