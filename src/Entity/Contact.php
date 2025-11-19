<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ContactRepository;
use App\Traits\Blameable;
use App\Traits\IsActive;
use App\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_CONTACT_LIST')"
        ),
        new Post(
            security: "is_granted('ROLE_CONTACT_CREATE')"
        ),
        new Get(
            security: "is_granted('ROLE_CONTACT_SHOW')"
        ),
        new Put(
            security: "is_granted('ROLE_CONTACT_UPDATE')"
        ),
        new Delete(
            security: "is_granted('ROLE_CONTACT_DELETE')"
        ),
    ],
    normalizationContext: ['groups' => ['contact_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['contact_write', 'is_active_write']],
    order: ['id' => 'DESC']
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'value' => 'ipartial',
        'contactType.name' => 'ipartial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'value',
        'contactType.name',
        'createdAt',
        'updatedAt',
    ]
)]
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'contact_read',
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'contact_read',
        'contact_write',
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private string $value;

    #[ORM\ManyToOne(targetEntity: ContactType::class)]
    #[Assert\NotNull]
    #[Groups([
        'contact_read',
        'contact_write',
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private ?ContactType $contactType = null;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'contacts')]
    #[Assert\NotBlank]
    #[Groups([
        'contact_read',
        'contact_write',
    ])]
    private Client $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setContactType(?ContactType $contactType): self
    {
        $this->contactType = $contactType;

        return $this;
    }

    public function getContactType(): ?ContactType
    {
        return $this->contactType;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
