<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\History\HistoryGetEntityCollectionAction;
use App\Repository\HistoryRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
        ),
        new GetCollection(
            uriTemplate: '/histories/{entity}/{entityId}',
            controller: HistoryGetEntityCollectionAction::class,
            normalizationContext: ['groups' => ['history_get_entity_collection']],
        ),
        new Get(
        ),
    ],
    normalizationContext: ['groups' => ['history_read']],
    order: ['id' => 'DESC']
)]
#[ApiFilter(
    DateFilter::class,
    properties: [
        'createdAt',
        'updatedAt',
        'loggedAt',
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'action' => 'ipartial',
        'objectId' => 'ipartial',
        'objectClass' => 'ipartial',
        'username' => 'ipartial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'action',
        'objectId',
        'objectClass',
        'username',
        'loggedAt',
    ]
)]
#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 8)]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected string $action;

    #[ORM\Column(type: 'datetime')]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected DateTimeInterface $loggedAt;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected ?string $objectId = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected string $objectClass;

    #[ORM\Column(type: 'integer')]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected int $version;

    #[ORM\Column(type: 'array', nullable: true)]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected ?array $data = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups([
        'history_read',
        'history_get_entity_collection',
    ])]
    protected ?string $username = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getLoggedAt(): DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(): self
    {
        $this->loggedAt = new DateTime();

        return $this;
    }

    public function getObjectId(): ?string
    {
        return $this->objectId;
    }

    public function setObjectId(?string $objectId): self
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function setObjectClass(string $objectClass): self
    {
        $this->objectClass = $objectClass;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }
}
