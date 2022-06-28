<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReportingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"="reporting_read"}
 * )
 * @ORM\Entity(repositoryClass=ReportingRepository::class)
 */
class Reporting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"reporting_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"reporting_read"})
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reportings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reporting_read"})
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="reportings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reporting_read"})
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
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
