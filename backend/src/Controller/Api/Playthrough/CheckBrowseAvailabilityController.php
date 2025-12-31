<?php

namespace App\Controller\Api\Playthrough;

use App\Repository\PlaythroughRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/playthrough/browse/availability', name: 'api_playthrough_browse_availability', methods: ['GET'])]
class CheckBrowseAvailabilityController extends AbstractController
{
    public function __construct(
        private readonly PlaythroughRepository $playthroughRepository
    ) {
    }

    public function __invoke(): JsonResponse
    {
        // Check if there are any completed playthroughs with videos
        $completedWithVideo = $this->playthroughRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.status = :status')
            ->andWhere('p.videoUrl IS NOT NULL')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        $hasRuns = $completedWithVideo > 0;

        return $this->json([
            'success' => true,
            'data' => [
                'available' => $hasRuns,
                'count' => (int) $completedWithVideo,
            ],
        ]);
    }
}
