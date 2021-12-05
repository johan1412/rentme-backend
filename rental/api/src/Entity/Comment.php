<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="comment_read"}
 * )
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user_read","product_read","comment_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_read","product_read","comment_read"})
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"user_read","product_read","comment_read"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"user_read","product_read","comment_read"})
     */
    private $rating;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @Groups({"comment_read"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="comments")
     * @Groups({"comment_read"})
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
