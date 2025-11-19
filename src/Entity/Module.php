<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ModuleRepository;
use App\Traits\Blameable;
use App\Traits\IsActive;
use App\Traits\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_MODULE_LIST')"
        ),
        new Post(
            security: "is_granted('ROLE_MODULE_CREATE')"
        ),
        new Get(
            security: "is_granted('ROLE_MODULE_SHOW')"
        ),
        new Put(
            security: "is_granted('ROLE_MODULE_UPDATE')"
        ),
        new Delete(
            security: "is_granted('ROLE_MODULE_DELETE')"
        ),
    ],
    normalizationContext: ['groups' => ['module_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['module_write', 'is_active_write']],
    order: ['id' => 'DESC']
)]
#[ApiFilter(
    DateFilter::class,
    properties: [
        'createdAt',
        'updatedAt',
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'name' => 'ipartial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'name',
        'createdAt',
        'updatedAt',
    ]
)]
#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'module_read',
        'group_read',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Groups([
        'module_read',
        'module_write',
        'group_read',
    ])]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'module', targetEntity: Role::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Assert\NotBlank]
    #[Groups([
        'module_read',
    ])]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setModule($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);

            if ($role->getModule() === $this) {
                $role->setModule(null);
            }
        }

        return $this;
    }
}
