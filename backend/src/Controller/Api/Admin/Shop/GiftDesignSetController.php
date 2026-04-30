<?php

namespace App\Controller\Api\Admin\Shop;

use App\DTO\Request\Admin\GiftDesignSetRequest;
use App\DTO\Response\Admin\GiftDesignSetResponse;
use App\Entity\UserDesignSet;
use App\Repository\DesignSetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin/shop/gift-design-set', name: 'api_admin_shop_gift_design_set', methods: ['POST'])]
class GiftDesignSetController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly DesignSetRepository $designSetRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payloadData = $request->toArray();
        /** @var array<string, mixed> $payloadData */
        $payload = GiftDesignSetRequest::fromArray($payloadData);
        $errors = $this->validator->validate($payload);
        if (count($errors) > 0) {
            return $this->json([
                'success' => false,
                'error' => ['message' => (string) $errors],
            ], 400);
        }

        $designSetId = $payload->designSetId;
        if ($designSetId === null) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'designSetId is required'],
            ], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $payload->userIdentifier])
            ?? $this->userRepository->findOneBy(['discordId' => $payload->userIdentifier]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'User not found'],
            ], 404);
        }

        // Find design set
        $designSet = $this->designSetRepository->find($designSetId);
        if (!$designSet) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'Design set not found'],
            ], 404);
        }

        // Check if user already owns this design set
        $existingOwnership = $this->entityManager->getRepository(UserDesignSet::class)->findOneBy([
            'userUuid' => $user->getUuid(),
            'designSet' => $designSet,
        ]);

        if ($existingOwnership) {
            return $this->json([
                'success' => false,
                'error' => ['message' => 'User already owns this design set'],
            ], 400);
        }

        // Create ownership record
        $userDesignSet = new UserDesignSet();
        $userDesignSet->setUserUuid($user->getUuid());
        $userDesignSet->setDesignSet($designSet);
        $userDesignSet->setPricePaid('0.00'); // Gifted
        $userDesignSet->setCurrency('USD');

        $this->entityManager->persist($userDesignSet);
        $this->entityManager->flush();

        return $this->json(
            GiftDesignSetResponse::fromMessage(
                sprintf(
                    'Design set "%s" gifted to %s',
                    $designSet->getDesignName()?->getName() ?? 'Unknown',
                    $user->getUsername()
                )
            )
        );
    }
}
