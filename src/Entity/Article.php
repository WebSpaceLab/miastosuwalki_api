<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Article
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['homepage:read', 'article:read', 'article:write', 'profile:read', 'admin:article:read', 'article:show',"gallery:read", "gallery:write", 'gallery:show'])]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['homepage:read', 'article:read', 'article:write', 'profile:read', 'admin:article:read', 'article:show', "gallery:read", "gallery:write", 'gallery:show'])]
    private ?string $title = null;

    #[Assert\NotBlank()]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?string $content = null;

    #[Assert\NotBlank()]
    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['homepage:read', 'admin:article:read', 'article:show', "gallery:read", "gallery:write", 'gallery:show'])]
    private ?string $slug = null;

    #[ORM\Column]
    #[Groups(['article:write', 'admin:article:read'])]
    private ?bool $isPublished = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[Groups(['admin:article:read', 'article:show'])]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['article:read', 'admin:article:read', 'article:show'])]
    private ?User $author = null;

    #[ORM\Column]
    private ?bool $isDelete = false;
    
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?Category $category = null;
    
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?Media $media = null;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?string $description = null;
    
    #[ORM\OneToOne(inversedBy: 'article', cascade: ['persist', 'remove'])]
    #[Groups(['homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?Gallery $gallery = null;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    #[Groups(['article:show', 'admin:article:read'])]
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

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

    #[Groups(['homepage:read', 'article:show', 'profile:read', 'admin:article:read'])]
    public function getIri()
    {
        return '/api/articles/' . $this->getId();
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

    #[Groups(['homepage:read', 'profile:read', 'admin:article:read', 'article:show'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['profile:read', 'admin:article:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): static
    {
        $this->gallery = $gallery;

        return $this;
    }
}
