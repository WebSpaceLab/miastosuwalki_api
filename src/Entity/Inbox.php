<?php

namespace App\Entity;

use App\Repository\InboxRepository;
use App\Trait\Timestamps;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InboxRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Inbox
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['admin:inbox:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:inbox:read'])]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:inbox:read'])]
    private ?string $sender = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:inbox:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(['admin:inbox:read'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['admin:inbox:read'])]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['admin:inbox:read'])]
    private ?bool $isRead = false;

    #[ORM\ManyToOne(inversedBy: 'inboxes')]
    private ?User $owner = null;

    #[ORM\Column]
    private ?bool $isDelete = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

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

    public function isIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): static
    {
        $this->isRead = $isRead;

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

    #[Groups(['admin:inbox:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return  Carbon::instance($this->createdAt)->diffForHumans();
    }

    #[Groups(['admin:inbox:read'])]
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
}
