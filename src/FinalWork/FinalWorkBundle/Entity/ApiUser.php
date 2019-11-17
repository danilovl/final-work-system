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

namespace FinalWork\FinalWorkBundle\Entity;

use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection
};
use Doctrine\ORM\Mapping as ORM;
use FinalWork\FinalWorkBundle\Constant\TranslationConstant;
use FinalWork\FinalWorkBundle\Entity\Traits\{
    IdTrait,
    CreateUpdateAbleTrait
};
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="api_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="api_key_unique", columns={"api_key"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="FinalWork\FinalWorkBundle\Entity\Repository\ApiUserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\Loggable()
 */
class ApiUser implements UserInterface
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="api_key", type="string", nullable=false, length=32)
     */
    protected $apiKey;

    /**
     * @var Collection|ApiUserRule[]
     *
     * @ORM\OneToMany(targetEntity="FinalWork\FinalWorkBundle\Entity\ApiUserRule", mappedBy="apiUser", cascade={"persist", "remove"})
     */
    protected $rules;

    /**
     * ApiUser constructor.
     */
    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Collection|ApiUserRule[]
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    /**
     * @param Collection $rules
     */
    public function setRules(Collection $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return ['ROLE_API'];
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return 'api_user';
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return 'api_user';
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername(): string
    {
        return 'api_user';
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName() ?? TranslationConstant::EMPTY;
    }
}
