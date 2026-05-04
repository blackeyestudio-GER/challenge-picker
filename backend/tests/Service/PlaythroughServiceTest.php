<?php

namespace App\Tests\Service;

use App\Entity\Playthrough;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PlaythroughRepository;
use App\Repository\RulesetRepository;
use App\Service\PlaythroughService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PlaythroughServiceTest extends TestCase
{
    public function testEndPlaythroughDiscardsShortRunsAutomatically(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $playthroughRepository = $this->createMock(PlaythroughRepository::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $rulesetRepository = $this->createMock(RulesetRepository::class);

        $service = $this->getMockBuilder(PlaythroughService::class)
            ->setConstructorArgs([$entityManager, $playthroughRepository, $gameRepository, $rulesetRepository])
            ->onlyMethods(['deleteShortCompletedPlaythrough'])
            ->getMock();

        $user = (new User())
            ->setEmail('user@example.com')
            ->setUsername('example-user');

        $playthrough = (new Playthrough())
            ->setUser($user)
            ->setStatus(Playthrough::STATUS_ACTIVE)
            ->setStartedAt(new \DateTimeImmutable('-90 seconds'));

        $service
            ->expects($this->once())
            ->method('deleteShortCompletedPlaythrough')
            ->with($playthrough, $user);

        $result = $service->endPlaythrough($playthrough);

        self::assertTrue($result['deleted']);
        self::assertNull($result['playthrough']);
        self::assertSame('Short run discarded automatically because it was under 3 minutes.', $result['message']);
    }

    public function testEndPlaythroughKeepsLongRuns(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $playthroughRepository = $this->createMock(PlaythroughRepository::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $rulesetRepository = $this->createMock(RulesetRepository::class);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $service = $this->getMockBuilder(PlaythroughService::class)
            ->setConstructorArgs([$entityManager, $playthroughRepository, $gameRepository, $rulesetRepository])
            ->onlyMethods(['deleteShortCompletedPlaythrough'])
            ->getMock();

        $service
            ->expects($this->never())
            ->method('deleteShortCompletedPlaythrough');

        $user = (new User())
            ->setEmail('user@example.com')
            ->setUsername('example-user');

        $playthrough = (new Playthrough())
            ->setUser($user)
            ->setStatus(Playthrough::STATUS_ACTIVE)
            ->setStartedAt(new \DateTimeImmutable('-10 minutes'));

        $result = $service->endPlaythrough($playthrough);

        self::assertFalse($result['deleted']);
        self::assertSame($playthrough, $result['playthrough']);
        self::assertGreaterThanOrEqual(PlaythroughService::SHORT_RUN_THRESHOLD_SECONDS, $playthrough->getTotalDuration());
        self::assertSame(Playthrough::STATUS_COMPLETED, $playthrough->getStatus());
    }
}
