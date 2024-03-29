<?php

namespace App\Entity;

use App\Repository\AboutRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AboutRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class About
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:about:read', 'homepage:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['admin:about:read', 'homepage:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['admin:about:read', 'homepage:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['admin:about:read'])]
    private ?bool $isActive = false;

    #[ORM\ManyToOne(inversedBy: 'abouts')]
    #[Groups(['admin:about:read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'abouts')]
    #[Groups(['admin:about:read', 'homepage:read'])]
    private ?Media $media = null;

    #[ORM\Column]
    #[Groups(['admin:about:read'])]
    private ?bool $isDelete = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function isIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(bool $isDelete): static
    {
        $this->isDelete = $isDelete;

        return $this;
    }

    #[Groups(['admin:about:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['admin:about:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
    }
}
