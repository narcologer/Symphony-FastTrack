<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an administrator',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $usernameQuestion = new Question('Username: ');
        $username = $helper->ask($input, $output, $usernameQuestion);

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $passwordQuestion);

        $admin = new Admin();

        $admin->setUsername($username);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, $password)
        );

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Admin "%s" created successfully.',
            $username
        ));

        return Command::SUCCESS;
    }
}