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
use App\Controller\Client\ClientGetItemAction;
use App\Controller\Client\ClientLoginByTokenCollectionAction;
use App\Controller\Client\ClientPutItemController;
use App\Controller\Client\ClientRemindPasswordCollectionController;
use App\Controller\Client\ClientSignupPostCollectionController;
use App\Interfaces\ClientInterface;
use App\Repository\ClientRepository;
use App\Traits\Blameable;
use App\Traits\IsActive;
use App\Traits\Timestampable;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use function bin2hex;
use function random_bytes;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['client_read_collection', 'read', 'is_active_read']],
            security: "is_granted('ROLE_CLIENT_LIST')"
        ),
        new Post(
            security: "is_granted('ROLE_CLIENT_CREATE')"
        ),
        new Post(
            uriTemplate: '/frontend/signup',
            controller: ClientSignupPostCollectionController::class,
            denormalizationContext: ['groups' => ['signup_collection']],
            validationContext: ['groups' => ['client_signup_frontend']],
            name: 'client_signup'
        ),
        new Get(
            security: "is_granted('ROLE_CLIENT_SHOW')"
        ),
        new Put(
            security: "is_granted('ROLE_CLIENT_UPDATE')"
        ),
        new Delete(
            security: "is_granted('ROLE_CLIENT_DELETE')"
        ),
        new Get(
            uriTemplate: '/frontend/profile/me',
            controller: ClientGetItemAction::class,
            normalizationContext: ['groups' => ['client_get_item']],
            security: "is_granted('ROLE_CLIENT')",
            read: false,
            deserialize: false,
            name: 'client_me_get'
        ),
        new Put(
            uriTemplate: '/frontend/profile/me',
            controller: ClientPutItemController::class,
            normalizationContext: ['groups' => ['client_put_item']],
            security: "is_granted('ROLE_CLIENT')",
            validationContext: ['groups' => ['client_put_frontend']],
            name: 'client_me_put'
        ),
        new Get(
            uriTemplate: '/frontend/login/{token}',
            controller: ClientLoginByTokenCollectionAction::class,
            read: false,
            deserialize: false,
            name: 'client_login_by_token'
        ),
        new Post(
            uriTemplate: '/frontend/remind/password',
            controller: ClientRemindPasswordCollectionController::class,
            read: false,
            deserialize: false,
            name: 'client_remind_password'
        ),
    ],
    normalizationContext: ['groups' => ['client_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['client_write', 'is_active_write']],
    order: ['id' => 'DESC']
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'name' => 'ipartial',
        'labels.id' => 'exact',
        'contacts.value' => 'ipartial',
        'description' => 'ipartial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'name',
        'description',
        'createdAt',
        'updatedAt',
    ]
)]
#[UniqueEntity(fields: ['username'], message: 'User already exists', errorPath: 'username')]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client implements ClientInterface, UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'client_read',
        'client_read_collection',
        'document_read',
        'project_read',
        'document_write',
        'project_write',
        'task_read',
        'contact_read',
        'contact_write',
        'order_header_read',
        'order_header_read_collection',
        'order_header_write',
        'address_read',
        'address_write',
        'client_get_item',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(
        groups: [
            'Default',
            'client_signup_frontend',
            'client_put_frontend',
        ]
    )]
    #[Groups([
        'client_read',
        'client_read_collection',
        'client_write',
        'document_read',
        'project_read',
        'document_write',
        'task_read',
        'contact_read',
        'order_header_read',
        'order_header_read_collection',
        'client_get_item',
        'client_put_item',
        'signup_collection',
        'address_read',
    ])]
    private string $name;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Address::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Assert\Valid]
    #[Groups([
        'client_read',
        'client_write',
    ])]
    private Collection $addresses;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Label::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Groups([
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private Collection $labels;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Contact::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Assert\Valid]
    #[Groups([
        'client_read',
        'client_read_collection',
        'client_write',
    ])]
    private Collection $contacts;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Project::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Assert\Valid]
    #[Groups([
        'document_read',
        'client_read',
        'client_write',
    ])]
    private Collection $projects;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Document::class, orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private Collection $documents;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(
        groups: [
            'Default',
            'client_signup_frontend',
            'client_put_frontend',
        ]
    )]
    #[Assert\Email(
        groups: [
            'Default',
            'client_signup_frontend',
            'client_put_frontend',
        ]
    )]
    #[Groups([
        'client_read',
        'client_write',
        'client_get_item',
        'client_put_item',
        'signup_collection',
    ])]
    private string $username;

    #[ORM\Column(type: 'string', length: 64)]
    private string $password;

    #[Assert\NotBlank(
        groups: [
            'client_signup_frontend',
        ]
    )]
    #[Groups([
        'client_write',
        'signup_collection',
    ])]
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $token = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTime $tokenCreatedAt = null;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->password = bin2hex(random_bytes(32));
        $this->labels = new ArrayCollection();
    }

    public function getClient(): self
    {
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
        }

        return $this;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setClient($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            if ($project->getClient() === $this) {
                $project->setClient(null);
            }
        }

        return $this;
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->addClient($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            $document->removeClient($this);
        }

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return ['ROLE_CLIENT'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenCreatedAt(): ?DateTime
    {
        return $this->tokenCreatedAt;
    }

    public function setTokenCreatedAt(?DateTime $tokenCreatedAt): void
    {
        $this->tokenCreatedAt = $tokenCreatedAt;
    }

    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(Label $label): self
    {
        if (!$this->labels->contains($label)) {
            $this->labels[] = $label;
        }

        return $this;
    }

    public function removeLabel(Label $label): self
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }
}
