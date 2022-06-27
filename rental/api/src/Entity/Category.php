<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"force_eager"=false},
 *     normalizationContext={"groups"="category_read"},
 *     denormalization_context={"groups"={"category_write"}},
 *
 *     collectionOperations={
 *         "get",
 *         "post",
 *
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('ROLE_ADMIN')"},
 *         "patch"={"security"="is_granted('ROLE_ADMIN')"},
 *         "delete"={"security"="is_granted('ROLE_ADMIN')"},
 *         "post"={"security"="is_granted('ROLE_ADMIN')"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"product_read","category_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"product_read","category_read", "category_write"})
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="category")
     * @Groups({"category_read"})
     */
    private $products;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="children")
     * @Groups({"category_read","category_write"})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="parent")
     * @Groups({"category_read"})
     */
    private $children;

    /**
     * @ORM\OneToOne(targetEntity=File::class, inversedBy="category", cascade={"persist", "remove"})
     * @Groups({"category_write","category_read"})
     */
    private $img;


    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->parent = new ArrayCollection();
        $this->children = new ArrayCollection();
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
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addCategory(self $category): self
    {
        if (!$this->children->contains($category)) {
            $this->children[] = $category;
            $category->setParent($this);
        }

        return $this;
    }

    public function removeCategory(self $category): self
    {
        if ($this->children->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getParent() === $this) {
                $category->setParent(null);
            }
        }

        return $this;
    }

    public function getImg(): ?File
    {
        return $this->img;
    }

    public function setImg(?File $img): self
    {
        $this->img = $img;

        return $this;
    }
}
