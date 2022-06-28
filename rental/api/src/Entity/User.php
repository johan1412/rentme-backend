<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="user_read"},
 *     denormalizationContext={"groups"={"user_write"}},
 *     collectionOperations={
 *         "get"={"security"="is_granted('ROLE_RENTER')"},
 *         "post"={},
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('USER_EDIT', user)"},
 *         "patch"={"security_post_denormalize"="is_granted('USER_EDIT', user)"},
 *         "delete"={"security_post_denormalize"="is_granted('USER_DELETE', user)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user_read","product_read","reservation_read","comment_read","reporting_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user_read","user_write","product_read","reservation_read","comment_read"})
     * @Assert\NotNull
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"user_read","user_write","product_read","reservation_read","comment_read"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"user_read", "user_write"})
     * @Assert\NotNull
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user_read","user_write","product_read","reservation_read","comment_read"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user_read","user_write","product_read","reservation_read","comment_read"})
     */
    private $lastName;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="user")
     * @Groups({"user_read","product_read"})
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="renter")
     * @Groups({"user_read"})
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="user")
     * @Groups({"user_read"})
     */
    private $comments;

    /**
     * @ORM\OneToOne(targetEntity=Address::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user_read","user_write","address_read"})
     */
    private $address;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="tenant")
     * @Groups({"user_read"})
     */
    private $transactions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stripeToken;

    /**
     * @ORM\OneToMany(targetEntity=Reporting::class, mappedBy="sender", orphanRemoval=true)
     */
    private $reportings;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->reportings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setSaller($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSaller() === $this) {
                $product->setSaller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setClient($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setClient($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getClient() === $this) {
                $comment->setClient(null);
            }
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Reservation $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setRenter($this);
        }

        return $this;
    }

    public function removeTransaction(Reservation $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getRenter() === $this) {
                $transaction->setRenter(null);
            }
        }

        return $this;
    }

    public function getStripeToken(): ?string
    {
        return $this->stripeToken;
    }

    public function setStripeToken(?string $stripeToken): self
    {
        $this->stripeToken = $stripeToken;

        return $this;
    }


    public function getFullName(): ?string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @return Collection<int, Reporting>
     */
    public function getReportings(): Collection
    {
        return $this->reportings;
    }

    public function addReporting(Reporting $reporting): self
    {
        if (!$this->reportings->contains($reporting)) {
            $this->reportings[] = $reporting;
            $reporting->setSender($this);
        }

        return $this;
    }

    public function removeReporting(Reporting $reporting): self
    {
        if ($this->reportings->removeElement($reporting)) {
            // set the owning side to null (unless already changed)
            if ($reporting->getSender() === $this) {
                $reporting->setSender(null);
            }
        }

        return $this;
    }
}
