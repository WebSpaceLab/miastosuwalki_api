<?php

namespace App\Entity;

use App\Repository\MetaTagsRepository;
use App\Trait\Timestamps;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MetaTagsRepository::class)]
class MetaTags
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['meta:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;
    
    #[Groups(['meta:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $content = null;

    #[Groups(['meta:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $charset = null;
    
    #[Groups(['meta:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $http_equiv = null;

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

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(?string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    public function getHttpEquiv(): ?string
    {
        return $this->http_equiv;
    }

    public function setHttpEquiv(?string $http_equiv): static
    {
        $this->http_equiv = $http_equiv;

        return $this;
    }
}
