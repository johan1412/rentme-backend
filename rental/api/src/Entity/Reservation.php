<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="reservation_read"},
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"={"security"="is_granted('ROLE_USER')"},
 *         "get_reservation_of_user"={
 *              "method"="GET",
 *              "path"="/reservations/user",
 *              "controller"=App\Controller\UserReservations::class
 *          },
 *          "get_reservation_of_renter"={
 *              "method"="GET",
 *              "path"="/reservations-renter",
 *              "controller"=App\Controller\RenterReservations::class
 *          },
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('RESERVATION_EDIT', reservation)"},
 *         "patch"={},
 *         "delete"={"security_post_denormalize"="is_granted('RESERVATION_DELETE', reservation)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=ReservationRepository::class)
 */
class Reservation
{

    const STATUS_PAYED = 'payed';
    const STATUS_RETRIEVED = 'retrieved';
    const STATUS_RESTORED = 'restored';

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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="transactions")
     * @Groups({"reservation_read"})
     * @Assert\NotNull
     */
    private $renter;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="reservations")
     * @Groups({"reservation_read"})
     * @Assert\NotNull
     */
    private $product;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"reservation_read"})
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"reservation_read","user_read","product_read"})
     */
    private $paymentIntent;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reservations")
     * @Groups({"reservation_read"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $tenant;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"reservation_read"})
     */
    private $isTransfered = false;

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
        if (!in_array($state, array(self::STATUS_PAYED, self::STATUS_RETRIEVED,self::STATUS_RESTORED))) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->state = $state;

        return $this;
    }

    public function getRenter(): ?User
    {
        return $this->renter;
    }

    public function setRenter(?User $renter): self
    {
        $this->renter = $renter;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPaymentIntent(): ?string
    {
        return $this->paymentIntent;
    }

    public function setPaymentIntent(string $paymentIntent): self
    {
        $this->paymentIntent = $paymentIntent;

        return $this;
    }

    public function getTenant(): ?User
    {
        return $this->tenant;
    }

    public function setTenant(?User $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    public function getIsTransfered(): ?bool
    {
        return $this->isTransfered;
    }

    public function setIsTransfered(?bool $isTransfered): self
    {
        $this->isTransfered = $isTransfered;

        return $this;
    }
}
