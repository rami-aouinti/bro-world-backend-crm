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
use App\Repository\RoleRepository;
use App\Traits\Blameable;
use App\Traits\IsActive;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
        ),
        new Post(
            ),
        new Get(
        ),
        new Put(
        ),
        new Delete(
        ),
    ],
    normalizationContext: ['groups' => ['role_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['role_write', 'is_active_write']],
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
    OrderFilter::class,
    properties: [
        'id',
        'name',
        'createdAt',
        'updatedAt',
    ]
)]
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'role_read',
        'group_read',
        'module_read',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'role_read',
        'role_write',
        'group_read',
        'module_read',
    ])]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Groups([
        'role_read',
        'role_write',
        'group_read',
        'module_read',
    ])]
    private string $role;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'roles')]
    #[Assert\NotBlank]
    #[Groups([
        'role_read',
        'role_write',
        'group_read',
    ])]
    private ?Module $module = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }
}
