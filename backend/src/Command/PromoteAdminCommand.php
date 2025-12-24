<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:admin:promote',
    description: 'Promote a user to admin by their Discord ID or email',
)]
class PromoteAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Discord ID or email of the user to promote')
            ->setHelp('This command allows you to promote a user to admin by their Discord ID or email. If no identifier is provided, it will list all users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $identifier = $input->getArgument('identifier');

        // If no identifier provided, list all users
        if (!$identifier) {
            $this->listUsers($io);

            return Command::SUCCESS;
        }

        // Find user by Discord ID or email
        $user = $this->findUser($identifier);

        if (!$user) {
            $io->error("No user found with Discord ID or email: {$identifier}");
            $io->note('Run this command without arguments to see all users.');

            return Command::FAILURE;
        }

        // Check if already admin
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $io->warning("User '{$user->getUsername()}' ({$user->getEmail()}) is already an admin!");

            return Command::SUCCESS;
        }

        // Promote to admin
        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);

        $this->entityManager->flush();

        $io->success("✅ User '{$user->getUsername()}' ({$user->getEmail()}) has been promoted to admin!");
        $io->info('Discord ID: ' . ($user->getDiscordId() ?? 'N/A'));

        return Command::SUCCESS;
    }

    private function findUser(string $identifier): ?User
    {
        // Try to find by Discord ID first
        $user = $this->userRepository->findOneBy(['discordId' => $identifier]);

        // If not found, try by email
        if (!$user) {
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
        }

        return $user;
    }

    private function listUsers(SymfonyStyle $io): void
    {
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $io->warning('No users found in the database.');

            return;
        }

        $io->title('Available Users');

        $rows = [];
        foreach ($users as $user) {
            $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true) ? '✅ Yes' : '❌ No';
            $rows[] = [
                $user->getUsername(),
                $user->getEmail(),
                $user->getDiscordId() ?? 'N/A',
                $isAdmin,
            ];
        }

        $io->table(
            ['Username', 'Email', 'Discord ID', 'Is Admin'],
            $rows
        );

        $io->note('To promote a user to admin, run: php bin/console app:admin:promote <discord_id_or_email>');
    }
}
