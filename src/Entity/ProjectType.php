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
use App\Repository\ProjectTypeRepository;
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
    normalizationContext: ['groups' => ['project_type_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['project_type_write', 'is_active_write']],
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
#[ORM\Entity(repositoryClass: ProjectTypeRepository::class)]
class ProjectType
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'project_type_read',
        'project_read',
        'project_write',
        'client_read',
        'client_write',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'project_type_read',
        'project_type_write',
        'project_read',
        'client_read',
    ])]
    private string $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
