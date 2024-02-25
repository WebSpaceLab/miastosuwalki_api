<?php

namespace App\Entity;

use App\Repository\AdvertisementRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertisementRepository::class)]
class Advertisement
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    #[Assert\Url]
    private ?string $imageUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    #[Assert\Url]
    private ?string $targetUrl = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin:advertisement:read'])]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin:advertisement:read'])]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(length: 55)]
    #[Groups(['admin:advertisement:read'])]

    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['admin:advertisement:read'])]
    private ?string $price = null;

    #[ORM\Column]
    #[Groups(['admin:advertisement:read', 'advertisement:read'])]
    private ?int $clicks = 0;

    #[ORM\Column(nullable: true)]
    private ?array $pagePaths = [];

    #[ORM\Column(nullable: true)]
    private ?array $adPositions = [];

    #[ORM\ManyToOne(inversedBy: 'advertisements')]
    private ?User $adding = null;

    #[ORM\Column]
    private ?bool $isActive = false;



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

    
    
    #[Groups(['admin:advertisement:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['admin:advertisement:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(?string $targetUrl): static
    {
        $this->targetUrl = $targetUrl;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getClicks(): ?int
    {
        return $this->clicks;
    }

    public function setClicks(int $clicks): static
    {
        $this->clicks = $clicks;

        return $this;
    }

    public function getPagePaths(): ?array
    {
        return $this->pagePaths;
    }

    public function setPagePaths(?array $pagePaths): static
    {
        $this->pagePaths = $pagePaths;

        return $this;
    }

    /**
     * Add a single page path
     *
     * @return self
     */
    public function addPagePath(string $pagePath): self
    {
        if (!in_array($pagePath, $this->pagePaths, true)) {
            $this->pagePaths[] = $pagePath;
        }

        return $this;
    }

    /**
     * Remove a single page path
     *
     * @return self
     */
    public function removePagePath(string $pagePath): self
    {
        if (($key = array_search($pagePath, $this->pagePaths, true)) !== false) {
            unset($this->pagePaths[$key]);
        }

        return $this;
    }

    public function getAdPositions(): ?array
    {
        return $this->adPositions;
    }

    public function setAdPositions(?array $adPositions): static
    {
        $this->adPositions = $adPositions;

        return $this;
    }

    /**
     * Add a single ad position
     *
     * @return self
     */
    public function addAdPosition(string $adPosition): self
    {
        if (!in_array($adPosition, $this->adPositions, true)) {
            $this->adPositions[] = $adPosition;
        }

        return $this;
    }

    /**
     * Remove a single ad position
     *
     * @return self
     */
    public function removeAdPosition(string $adPosition): self
    {
        if (($key = array_search($adPosition, $this->adPositions, true)) !== false) {
            unset($this->adPositions[$key]);
        }

        return $this;
    }

    public function getAdding(): ?User
    {
        return $this->adding;
    }

    public function setAdding(?User $adding): static
    {
        $this->adding = $adding;

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
