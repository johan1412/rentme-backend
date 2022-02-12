<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="product_read"},
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *          "number_product_not_valid"={
 *              "method"="GET",
 *              "path"="/products/products-not-valid",
 *              "controller"=App\Controller\ProductsNotValid::class
 *          },
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('PRODUCT_EDIT', product)"},
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"}
 *     }
 * )
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */

// "patch"={"security_post_denormalize"="is_granted('PRODUCT_EDIT', product)"}, a remettre
// "delete"={"security_post_denormalize"="is_granted('PRODUCT_DELETE', product)"},
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user_read","product_read","reservation_read","file_read","comment_read","category_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_read","product_read","reservation_read","file_read","comment_read","category_read"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 200
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_read","product_read","reservation_read","file_read","comment_read","category_read"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 1000
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user_read","product_read","reservation_read","file_read","comment_read","category_read"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 500
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"user_read","product_read","reservation_read","file_read","comment_read","category_read"})
     * @Assert\NotBlank
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="products")
     * @Groups({"product_read"})
     * @Assert\NotNull
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="product")
     * @Groups({"product_read"})
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=File::class, mappedBy="product")
     * @Groups({"product_read"})
     */
    private $files;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"product_read"})
     * @Assert\NotNull
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="product")
     * @Groups({"product_read"})
     */
    private $reservations;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"product_read"})
     */
    private $isValid = false;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
            $comment->setProduct($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getProduct() === $this) {
                $comment->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setProduct($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getProduct() === $this) {
                $file->setProduct(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
            $reservation->setProduct($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getProduct() === $this) {
                $reservation->setProduct(null);
            }
        }

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }
}
