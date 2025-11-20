<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DocumentRepository;
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
        ),
        new Post(),
        new Get(),
        new Put(),
        new Delete(
        ),
    ],
    normalizationContext: ['groups' => ['document_read', 'read', 'is_active_read']],
    denormalizationContext: ['groups' => ['document_write', 'is_active_write']],
    order: ['id' => 'DESC']
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'name' => 'partial',
        'client' => 'partial',
    ]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'id',
        'name',
        'client',
        'createdAt',
        'updatedAt',
    ]
)]
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    use Timestampable;
    use Blameable;
    use IsActive;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'document_read',
        'project_read',
        'invoice_header_read',
        'invoice_header_write',
        'invoice_header_read',
    ])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups([
        'document_read',
        'document_write',
        'project_read',
        'invoice_header_read',
    ])]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'documents')]
    #[Groups([
        'document_read',
        'document_write',
    ])]
    private ?Client $client = null;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'documents')]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Groups([
        'document_read',
        'document_write',
    ])]
    private Collection $projects;

    #[ApiProperty(types: ['http://schema.org/image'])]
    #[ORM\ManyToMany(targetEntity: File::class)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    #[Groups([
        'document_read',
        'document_write',
        'project_read',
    ])]
    public Collection $files;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->files = new ArrayCollection();
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

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->addDocument($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            $project->removeDocument($this);
        }

        return $this;
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
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
