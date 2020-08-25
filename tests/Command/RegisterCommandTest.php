<?php

namespace App\Tests\Command;

use App\Command\RegisterCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ValidatorService;
use Doctrine\Bundle\DoctrineBundle\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RegisterCommandTest extends TestCase
{
    public function testExecute()
    {
        $id = 1;
        $email = 'user@gmail.com';

        $user = new User();
        $user->setId($id);
        $user->setName('Name');
        $user->setEmail($email);

        $doctrineMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validatorServiceMock = $this->getMockBuilder(ValidatorService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $application = new Application();
        $application->add(new RegisterCommand($validatorServiceMock,$doctrineMock, null));

        $command = $application->find('register-user');

        $commandTester = new CommandTester($command);

        $validatorServiceMock->expects($this->once())
            ->method('validateAdminData')
            ->withAnyParameters()
            ->willReturn(true);

        /** @var UserRepository|MockObject $userRepositoryMock */
        $userRepositoryMock = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userRepositoryMock
            ->method('findOneBy')
            ->with([$email])
            ->willReturn($user);

        $result = $commandTester->execute([
                '-f' => 'Test,User',
                '-m' => $email,
        ]);

        $this->assertEquals(0, $result);
    }
}
