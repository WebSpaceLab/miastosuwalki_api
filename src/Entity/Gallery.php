<?php

namespace App\Entity;

use App\Repository\GalleryRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Gallery
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show','homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show','homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show', 'homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'galleries')]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show', 'homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    #[ORM\JoinTable(name: 'gallery_media')]
    private Collection $media;

    #[ORM\Column]
    #[Groups(["gallery:read", "gallery:write"])]
    private ?bool $isPublished = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDelete = false;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show','homepage:read', 'article:write', 'admin:article:read', 'article:show'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'galleries')]

    private ?User $author = null;
    
    #[ORM\OneToOne(mappedBy: 'gallery', cascade: ['persist', 'remove'])]
    #[Groups(["gallery:read", "gallery:write", 'gallery:show'])]
    private ?Article $article = null;



    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->media = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            // $this->media[] = $medium;
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        $this->media->removeElement($medium);

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

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function isIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(?bool $isDelete): static
    {
        $this->isDelete = $isDelete;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function __toString()
    {
        // zwraca unikalny identyfikator lub inną właściwość encji
        return $this->title;
    }

    #[Groups(["gallery:read", "gallery:write", 'gallery:show'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(["gallery:read", "gallery:write", 'gallery:show'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        // unset the owning side of the relation if necessary
        if ($article === null && $this->article !== null) {
            $this->article->setGallery(null);
        }

        // set the owning side of the relation if necessary
        if ($article !== null && $article->getGallery() !== $this) {
            $article->setGallery($this);
        }

        $this->article = $article;

        return $this;
    }


}
