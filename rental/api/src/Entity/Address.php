<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="address_read"},
 *     collectionOperations={
 *         "get",
 *         "post",
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('USER_EDIT', user)"},
 *         "patch"={"security_post_denormalize"="is_granted('USER_EDIT', user)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"address_read","user_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"address_read","user_read", "user_write", "product_write"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 200
     * )
     */
    private $streetName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"address_read","user_read", "user_write", "product_write"})
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 1,
     *     max = 50
     * )
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class, cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"address_read","user_read", "user_write", "product_write"})
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="address")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setAddress($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getAddress() === $this) {
                $product->setAddress(null);
            }
        }

        return $this;
    }
}
