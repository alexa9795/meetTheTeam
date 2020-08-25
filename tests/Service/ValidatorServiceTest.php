<?php

namespace App\Tests\Service;

use App\Service\ValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorServiceTest extends TestCase
{
    public function testValidateAdminData()
    {

        $email = 'user@gmail.com';
        $fullName = ' ';

        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validatorMock
            ->method('validate')
            ->withAnyParameters()
            ->willReturn(true);

        /** @var ValidatorService|MockObject $validatorServiceMock */
        $validatorServiceMock = $this->getMockBuilder(ValidatorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $validatorServiceMock->validateAdminData($email, $fullName);

        $this->assertFalse($result);
    }
}
