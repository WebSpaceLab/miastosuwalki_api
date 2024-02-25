<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user-create',
    description: 'Create a user with password',
)]
class UserCreateCommand extends Command
{
    public function __construct(private UserRepository $userRepository, private UserPasswordHasherInterface $passwordHasher) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username of the user')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email of the user')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password of the user in plan text')
            ->addArgument('roles', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Roles of the user separated by space')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roles = $input->getArgument('roles');

        if($this->userRepository->findOneByUsername($username)) {
            $io->error('User with this username already exists');
            return Command::FAILURE;
        }
        
        if($this->userRepository->findOneByUsername($email)) {
            $io->error('User with this email already exists');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setPassword($hashedPassword);
        $user->setRoles($roles);

        $this->userRepository->save($user, true);

        $io->success('User was created with ID '. $user->getId());
        $io->info(var_export($user, true));

        return Command::SUCCESS;
    }
}
