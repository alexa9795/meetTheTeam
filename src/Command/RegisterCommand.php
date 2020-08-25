<?php

namespace App\Command;

use App\Entity\User;
use App\Service\ValidatorService;
use Doctrine\Persistence\ManagerRegistry as Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterCommand extends Command
{
    /** @var ValidatorService */
    private $validatorService;
    /** @var Registry */
    private $doctrine;

    public function __construct(ValidatorService $validatorService, Registry $doctrine, string $name = null)
    {
        $this->validatorService = $validatorService;
        $this->doctrine = $doctrine;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('register-user')
            ->setDescription('As an admin I can register a new user');

        $this->addOption('fullName', 'f', InputOption::VALUE_REQUIRED, 'User full name (separate multiple names with a comma)');
        $this->addOption('email', 'm', InputOption::VALUE_REQUIRED, 'User email');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $names = $input->getOption('fullName');
        $fullName = str_replace(',', ' ', $names);

        $email = $input->getOption('email');

        if (!$this->validatorService->validateAdminData($email, $fullName)) {
            throw new \Exception('Invalid argument passed.');
        }

        try {
            /** @var EntityManager $em */
            $em = $this->doctrine->getManager();

            $user = (new User());

            $user->setName($fullName);
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('default');
            $user->setCreatedBy('admin');

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (empty($user)) {
                $em->persist($user);
                $em->flush();

                $output->writeln([
                    'New user created with id ' . $user->getId()
                ]);
            } else {
                $output->writeln([
                    'User exists for email ' . $user->getEmail()
                ]);
            }
        } catch (\Throwable $e) {
            $output->writeln([
                $e->getMessage()
            ]);
        }
    }
}
