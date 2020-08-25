<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateAdminData(string $email, string $fullName): bool
    {
        $input = ['name' => $fullName, 'email' => $email];

        $constraints = new Assert\Collection([
            'name' => [new Assert\Length(['min' => 2]), new Assert\NotBlank],
            'email' => [new Assert\Email(), new Assert\notBlank],
        ]);

        $violations = $this->validator->validate($input, $constraints);

        if (count($violations) === 0) {
            return true;
        }

        return false;
    }
}
