<?php

namespace App\Entity;

use App\Repository\PricePackageRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PricePackageRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class PricePackage
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['price:read', 'admin:price:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?bool $isHighlighted = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $actionUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $nameBtn = null;

    #[ORM\OneToMany(mappedBy: 'pricePackage', targetEntity: PricePackageInformation::class)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private Collection $information;

    #[ORM\ManyToOne(inversedBy: 'packages')]
    #[Groups(['admin:price:read', 'admin:price:write'])]
    private ?Price $priceList = null;

    #[ORM\Column]
    private ?bool $isDelete = false;

    #[ORM\Column]
    #[Groups(['admin:price:read', 'admin:price:write'])]
    private ?bool $isActive = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->information = new ArrayCollection();
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

    public function isIsHighlighted(): ?bool
    {
        return $this->isHighlighted;
    }

    public function setIsHighlighted(bool $isHighlighted): static
    {
        $this->isHighlighted = $isHighlighted;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(?string $actionUrl): static
    {
        $this->actionUrl = $actionUrl;

        return $this;
    }

    public function getNameBtn(): ?string
    {
        return $this->nameBtn;
    }

    public function setNameBtn(?string $nameBtn): static
    {
        $this->nameBtn = $nameBtn;

        return $this;
    }

    /**
     * @return Collection<int, PricePackageInformation>
     */
    public function getInformation(): Collection
    {
        return $this->information;
    }

    public function addInformation(PricePackageInformation $information): static
    {
        if (!$this->information->contains($information)) {
            $this->information->add($information);
            $information->setPricePackage($this);
        }

        return $this;
    }

    public function removeInformation(PricePackageInformation $information): static
    {
        if ($this->information->removeElement($information)) {
            // set the owning side to null (unless already changed)
            if ($information->getPricePackage() === $this) {
                $information->setPricePackage(null);
            }
        }

        return $this;
    }

    public function getPriceList(): ?Price
    {
        return $this->priceList;
    }

    public function setPriceList(?Price $priceList): static
    {
        $this->priceList = $priceList;

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

    public function isIsDelete(): ?bool
    {
        return $this->isDelete;
    }

    public function setIsDelete(bool $isDelete): static
    {
        $this->isDelete = $isDelete;

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
