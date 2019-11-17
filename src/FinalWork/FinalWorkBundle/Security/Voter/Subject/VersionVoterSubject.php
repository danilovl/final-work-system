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

namespace FinalWork\FinalWorkBundle\Security\Voter\Subject;

use FinalWork\FinalWorkBundle\Entity\{
    Work,
    Media
};

class VersionVoterSubject
{
    /**
     * @var Work|null
     */
    protected $work;

    /**
     * @var Media|null
     */
    protected $media;

    /**
     * @return Work|null
     */
    public function getWork(): ?Work
    {
        return $this->work;
    }

    /**
     * @param Work|null $work
     * @return VersionVoterSubject
     */
    public function setWork(?Work $work): self
    {
        $this->work = $work;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getMedia(): ?Media
    {
        return $this->media;
    }

    /**
     * @param Media|null $media
     * @return VersionVoterSubject
     */
    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }
}