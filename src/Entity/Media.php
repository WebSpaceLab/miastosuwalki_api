<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Media
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['media:read', 'homepage:read', 'admin:media:read', 'admin:about:read',  'admin:feature:read', 'admin:hero:read', 'admin:team:read',  'admin:article:read', 'article:show',"gallery:read", "gallery:write", 'gallery:show'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media:read', 'homepage:read', 'admin:media:read', 'admin:about:read',  'admin:feature:read', 'admin:hero:read', 'admin:team:read', 'admin:article:read', 'article:show', "gallery:read", "gallery:write", 'gallery:show'])] 
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read', 'admin:media:read', 'admin:about:read', 'admin:feature:read', 'feature:read', 'admin:hero:read','admin:team:read', "gallery:read", "gallery:write", 'gallery:show'])] 
    private ?string $fileName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read', 'admin:media:read', "gallery:read", "gallery:write", 'gallery:show'])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['media:read', 'admin:media:read', "gallery:read", "gallery:write"])]
    private ?int $size = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['media:read', 'admin:media:read', "gallery:read", "gallery:write", 'gallery:show'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[Groups(['admin:media:read'])]
    private ?User $author = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin:media:read'])]
    private ?bool $isDelete = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin:media:read'])]
    private ?bool $isUsed = false;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: About::class)]
    private Collection $abouts;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: Team::class)]
    private Collection $teams;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read', 'admin:media:read'])]
    private ?string $pathUrl = null;

    // #[ORM\ManyToMany(targetEntity: Gallery::class, mappedBy: 'media')]
    // #[ORM\JoinTable(name: 'gallery_media')]
    // private Collection $galleries;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->abouts = new ArrayCollection();
        $this->teams = new ArrayCollection();
        // $this->galleries = new ArrayCollection();
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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

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

    #[Groups(['media:read', 'admin:media:read'])]
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    #[Groups(['media:read', 'admin:media:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['media:read', 'admin:media:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
    }

    #[Groups(['homepage:read', 'article:show', 'media:read', 'admin:media:read', 'admin:article:read', 'admin:about:read', 'admin:feature:read', 'feature:read', 'admin:hero:read','admin:team:read', "gallery:read", "gallery:write", 'gallery:show'])]
    public function getPreviewUrl(): ?string
    {
        return 'https://localhost:8000' . $this->filePath;
    }

    public function setPreviewUrl($url): self
    {
        $this->filePath = $url;

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

    public function isIsUsed(): ?bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(?bool $isUsed): static
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * @return Collection<int, About>
     */
    public function getAbouts(): Collection
    {
        return $this->abouts;
    }

    public function addAbout(About $about): static
    {
        if (!$this->abouts->contains($about)) {
            $this->abouts->add($about);
            $about->setMedia($this);
        }

        return $this;
    }

    public function removeAbout(About $about): static
    {
        if ($this->abouts->removeElement($about)) {
            // set the owning side to null (unless already changed)
            if ($about->getMedia() === $this) {
                $about->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->setMedia($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            // set the owning side to null (unless already changed)
            if ($team->getMedia() === $this) {
                $team->setMedia(null);
            }
        }

        return $this;
    }

    public function getPathUrl(): ?string
    {
        return $this->pathUrl;
    }

    public function setPathUrl(?string $pathUrl): static
    {
        $this->pathUrl = $pathUrl;

        return $this;
    }

    /**
     * @return Collection<int, Gallery>
     */
    // public function getGalleries(): Collection
    // {
    //     return $this->galleries;
    // }

    // public function addGallery(Gallery $gallery): static
    // {
    //     if (!$this->galleries->contains($gallery)) {
    //         $this->galleries->add($gallery);
    //         $gallery->addMedium($this);
    //     }

    //     return $this;
    // }

    // public function removeGallery(Gallery $gallery): static
    // {
    //     if ($this->galleries->removeElement($gallery)) {
    //         $gallery->removeMedium($this);
    //     }

    //     return $this;
    // }

    
    public function __toString()
    {
        // zwraca unikalny identyfikator lub inną właściwość encji
        return $this->name;
    }
}
