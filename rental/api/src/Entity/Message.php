<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="comment_read"},
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     collectionOperations={
 *         "post"={"security"="is_granted('ROLE_USER')"}
 *     },
 *     itemOperations={
 *         "get"={"security_post_denormalize"="object.getSender() == user"},
 *         "put"={"security_post_denormalize"="object.getSender() == user and previous_object.getSender() == user"},
 *         "patch"={"security_post_denormalize"="object.getSender() == user and previous_object.getSender() == user"},
 *         "delete"={"security_post_denormalize"="is_granted('ROLE_ADMIN') or object.getSender() == user"},
 *     }
 * )
 * @ORM\Entity(repositoryClass=MessageRepository::class)
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $reciever;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="messages")
     */
    private $product;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $text;

    /**
     * @ORM\Column(type="boolean",options={"default":false})
     */
    private $isRead;


    public function __construct()
    {
        $this->isRead = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReciever(): ?User
    {
        return $this->reciever;
    }

    public function setReciever(?User $reciever): self
    {
        $this->reciever = $reciever;

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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}
