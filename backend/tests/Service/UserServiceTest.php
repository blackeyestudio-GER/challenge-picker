<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\UserService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    public function testDeleteAccountRequiresPasswordForPasswordUsers(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $emailService = $this->createMock(EmailService::class);

        $service = new UserService($entityManager, $userRepository, $passwordHasher, $emailService);
        $user = (new User())
            ->setEmail('user@example.com')
            ->setUsername('example-user')
            ->setPassword('hashed-password');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Current password is required to delete your account');

        $service->deleteAccount($user, null);
    }

    public function testDeleteAccountScrubsAccountAndRevokesState(): void
    {
        $connection = $this->createMock(Connection::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $emailService = $this->createMock(EmailService::class);

        $service = new UserService($entityManager, $userRepository, $passwordHasher, $emailService);
        $user = (new User())
            ->setEmail('user@example.com')
            ->setUsername('example-user')
            ->setPassword('hashed-password')
            ->setOauthProvider('discord')
            ->setOauthId('discord-user')
            ->setDiscordId('discord-id')
            ->setTwitchId('twitch-id')
            ->setTheme('light')
            ->setRoles(['ROLE_ADMIN']);

        $passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'secret')
            ->willReturn(true);

        $entityManager
            ->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);

        $entityManager
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $connection
            ->expects($this->exactly(7))
            ->method('executeStatement')
            ->willReturn(1);

        $service->deleteAccount($user, 'secret');

        self::assertStringStartsWith('deleted+', $user->getEmail());
        self::assertStringStartsWith('deleted-user-', $user->getUsername());
        self::assertNull($user->getPassword());
        self::assertNull($user->getOauthProvider());
        self::assertNull($user->getOauthId());
        self::assertNull($user->getDiscordId());
        self::assertNull($user->getTwitchId());
        self::assertNull($user->getTheme());
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertFalse($user->isPasswordUser());
    }
}
