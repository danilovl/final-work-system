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

namespace App\Domain\ApiUser\Entity;

use App\Application\Exception\RuntimeException;
use App\Application\Traits\Entity\{
    IdTrait,
    CreateUpdateAbleTrait
};
use App\Domain\ApiUser\Repository\ApiUserRepository;
use App\Domain\ApiUserRule\Entity\ApiUserRule;
use Doctrine\Common\Collections\{
    ArrayCollection,
    Collection
};
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\{
    UserInterface,
    PasswordAuthenticatedUserInterface
};

#[ORM\Table(name: 'api_user')]
#[ORM\UniqueConstraint(name: 'api_key_unique', columns: ['api_key'])]
#[ORM\Entity(repositoryClass: ApiUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Gedmo\Loggable]
class ApiUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    use IdTrait;
    use CreateUpdateAbleTrait;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'api_key', type: Types::STRING, length: 32, nullable: false)]
    protected string $apiKey;

    /** @var Collection<ApiUserRule> */
    #[ORM\OneToMany(targetEntity: ApiUserRule::class, mappedBy: 'apiUser', cascade: ['persist', 'remove'])]
    protected Collection $rules;

    public function __construct()
    {
        $this->rules = new ArrayCollection;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection<ApiUserRule>
     */
    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function setRules(Collection $rules): void
    {
        $this->rules = $rules;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getUserIdentifier(): string
    {
        if (empty($this->getUsername())) {
            throw new RuntimeException('Username is empty.');
        }

        return $this->getUsername();
    }

    public function getRoles(): array
    {
        return ['ROLE_API'];
    }

    public function getPassword(): string
    {
        return 'api_user';
    }

    public function getSalt(): ?string
    {
        return 'api_user';
    }

    public function getUsername(): string
    {
        return 'api_user';
    }

    public function eraseCredentials(): void {}

    public function __toString(): string
    {
        return $this->name;
    }
}
