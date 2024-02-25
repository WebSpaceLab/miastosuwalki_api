<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use App\Trait\Timestamps;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Comment
{
    use Timestamps;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    #[Groups(['comment:write', 'comment:read', 'article:read', 'profile:read'])]
    private ?string $name = null;
    
    #[Assert\NotBlank()]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['comment:write', 'comment:read', 'article:read', 'profile:read'])]
    private ?string $content = null;
    
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[Groups(['profile:read'])]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[Groups(['comment:write', 'article:read'])]
    private ?User $owner = null;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
