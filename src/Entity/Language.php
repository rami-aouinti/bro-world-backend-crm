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
use App\Repository\LanguageRepository;
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
    normalizationContext: ['groups' => ['language_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['language_write', 'is_active_write']],
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
        'code' => 'ipartial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'name',
        'code',
        'createdAt',
        'updatedAt',
    ]
)]
#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'language_read',
        'invoice_header_read',
        'invoice_header_write',
        'user_read',
        'user_read_collection',
        'user_write',
        'category_read',
        'category_write',
        'product_read',
        'product_write',
        'text_read',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'language_read',
        'language_write',
        'invoice_header_read',
        'user_read',
        'user_read_collection',
        'category_read',
        'product_read',
        'text_read',
    ])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'language_read',
        'language_write',
        'category_read',
        'product_read',
        'category_read_frontend',
        'text_read',
    ])]
    private string $code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
