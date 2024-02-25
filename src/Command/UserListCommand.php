<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-list',
    description: 'List all available users in the database',
)]
class UserListCommand extends Command
{
    public function __construct(private UserRepository $userRepository) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $users = $this->userRepository->findAll();
        $table->setHeaders(['ID', 'Username', 'Email', 'Roles']);
        $table->setRows(array_map(fn (User $user) => [
            $user->getId(), $user->getUsername(), $user->getEmail(), implode(', ', $user->getRoles())
        ], $users));
        $table->render();

        return Command::SUCCESS;
    }
}
