<?php
// src/DTO/RegisterRequest.php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $firstname;

    #[Assert\NotBlank]
    public string $lastname;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public string $password;
}