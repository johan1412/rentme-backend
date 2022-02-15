<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="file_read"},
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "get",
 *         "post"={"security_post_denormalize"="is_granted('FILE_CREATE', file)"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security_post_denormalize"="is_granted('FILE_EDIT', file)"},
 *         "patch"={"security_post_denormalize"="is_granted('FILE_EDIT', file)"},
 *         "delete"={"security_post_denormalize"="is_granted('FILE_DELETE', file)"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"product_read","file_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, options={"default" : "ntm"})
     * @Groups({"product_read","file_read","product_write", "user_read"})
     * @Assert\NotBlank
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="files", cascade={"persist"})
     * @Groups({"file_read", "product_write"})
     * @Assert\NotBlank
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

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
