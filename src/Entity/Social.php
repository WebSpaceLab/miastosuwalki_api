<?php

namespace App\Entity;

use App\Repository\SocialRepository;
use App\Trait\Timestamps;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SocialRepository::class)]
class Social
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['social:read'])]
    private ?int $id = null;

    #[Groups(['social:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['social:read'])]
    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    #[Groups(['social:read'])]
    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column]
    #[Groups(['social:read'])]
    private ?bool $isActive = null;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

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
}
