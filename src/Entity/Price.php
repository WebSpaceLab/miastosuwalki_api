<?php

namespace App\Entity;

use App\Repository\PriceRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Price
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['price:read', 'admin:price:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $subtitle = null;

    #[ORM\Column]
    #[Groups(['admin:price:read', 'admin:price:write'])]
    private ?bool $isActive = false;

    #[ORM\OneToMany(mappedBy: 'priceList', targetEntity: PricePackage::class)]
    #[Groups(['price:read', 'admin:price:read'])]
    private Collection $packages;

    #[ORM\Column]
    private ?bool $isDelete = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->packages = new ArrayCollection();
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

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

    #[Groups(['admin:price:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['admin:price:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        $updatedAtAgo = $this->updatedAt;

        if ($updatedAtAgo) {
            $updatedAtAgo = Carbon::instance($updatedAtAgo)->diffForHumans();
        }

        return  $updatedAtAgo;
    }

    /**
     * @return Collection<int, PricePackage>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(PricePackage $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setPriceList($this);
        }

        return $this;
    }

    public function removePackage(PricePackage $package): static
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getPriceList() === $this) {
                $package->setPriceList(null);
            }
        }

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
}
