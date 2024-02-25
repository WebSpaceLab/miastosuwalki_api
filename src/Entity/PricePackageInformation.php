<?php

namespace App\Entity;

use App\Repository\PricePackageInformationRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PricePackageInformationRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class PricePackageInformation
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:price:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?string $info = null;

    #[ORM\Column]
    #[Groups(['price:read', 'admin:price:read', 'admin:price:write'])]
    private ?bool $isHighlighted = false;

    #[ORM\ManyToOne(inversedBy: 'information')]
    #[Groups(['admin:price:write'])]
    private ?PricePackage $pricePackage = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info): static
    {
        $this->info = $info;

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

    public function getPricePackage(): ?PricePackage
    {
        return $this->pricePackage;
    }

    public function setPricePackage(?PricePackage $pricePackage): static
    {
        $this->pricePackage = $pricePackage;

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
}
