<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="reservation_read"},
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('RESERVATION_EDIT', reservation)"},
 *         "patch"={"security_post_denormalize"="is_granted('RESERVATION_EDIT', reservation)"},
 *         "delete"={"security_post_denormalize"="is_granted('RESERVATION_DELETE', reservation)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"reservation_read","user_read","product_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reservation_read","user_read","product_read"})
     * @Assert\NotNull
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reservation_read","user_read","product_read"})
     * @Assert\NotNull
     */
    private $rentalBeginDate;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reservation_read","user_read","product_read"})
     * @Assert\NotNull
     */
    private $rentalEndDate;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"reservation_read","user_read","product_read"})
     * @Assert\NotBlank
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations")
     * @Groups({"reservation_read"})
     * @Assert\NotNull
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="reservations")
     * @Groups({"reservation_read"})
     * @Assert\NotNull
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRentalBeginDate(): ?\DateTimeInterface
    {
        return $this->rentalBeginDate;
    }

    public function setRentalBeginDate(\DateTimeInterface $rentalBeginDate): self
    {
        $this->rentalBeginDate = $rentalBeginDate;

        return $this;
    }

    public function getRentalEndDate(): ?\DateTimeInterface
    {
        return $this->rentalEndDate;
    }

    public function setRentalEndDate(\DateTimeInterface $rentalEndDate): self
    {
        $this->rentalEndDate = $rentalEndDate;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

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
