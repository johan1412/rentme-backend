<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class User
{
    /**
     * @var int The entity Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string First name
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $firstName = '';

    /**
     * @var string Last name
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $lastName = '';

     /**
     * @var string Email
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $email = '';

    /**
     * @var string Adress
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $adress = '';

    /**
     * @var string Birth Date
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $birthDate = '';

    /**
     * @var string Phone Number
     *
     * @ORM\Column
     * @Assert\NotBlank
     */
    public $phoneNumber = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): int
    {
        return $this->firstName;
    }

    public function getLastName(): int
    {
        return $this->lastName;
    }

    public function getEmail(): int
    {
        return $this->email;
    }

    public function getBirthDate(): int
    {
        return $this->birthDate;
    }

    public function getPhoneNumber(): int
    {
        return $this->phoneNumber;
    }
}
