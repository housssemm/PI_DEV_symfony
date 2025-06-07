<?php

namespace App\Entity;

use App\Repository\ProgressPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProgressPostRepository::class)]
class ProgressPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'progressPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column]
    private ?float $currentWeight = null;

    #[ORM\Column]
    private ?float $goalWeight = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $beforeImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $afterImage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isPublic = true;

    #[ORM\Column]
    private ?int $likes = 0;

    #[ORM\Column]
    private ?int $comments = 0;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
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

    public function getCurrentWeight(): ?float
    {
        return $this->currentWeight;
    }

    public function setCurrentWeight(float $currentWeight): static
    {
        $this->currentWeight = $currentWeight;
        return $this;
    }

    public function getGoalWeight(): ?float
    {
        return $this->goalWeight;
    }

    public function setGoalWeight(float $goalWeight): static
    {
        $this->goalWeight = $goalWeight;
        return $this;
    }

    public function getBeforeImage(): ?string
    {
        return $this->beforeImage;
    }

    public function setBeforeImage(?string $beforeImage): static
    {
        $this->beforeImage = $beforeImage;
        return $this;
    }

    public function getAfterImage(): ?string
    {
        return $this->afterImage;
    }

    public function setAfterImage(?string $afterImage): static
    {
        $this->afterImage = $afterImage;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function getComments(): ?int
    {
        return $this->comments;
    }

    public function setComments(int $comments): static
    {
        $this->comments = $comments;
        return $this;
    }
}
