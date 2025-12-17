<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserObsPreference;
use App\Repository\UserObsPreferenceRepository;
use App\DTO\Request\Obs\UpdateObsPreferenceRequest;

class ObsPreferenceService
{
    public function __construct(
        private UserObsPreferenceRepository $preferenceRepository
    ) {
    }

    /**
     * Get or create OBS preferences for a user
     */
    public function getOrCreatePreferences(User $user): UserObsPreference
    {
        $preference = $this->preferenceRepository->findByUser($user);

        if (!$preference) {
            $preference = new UserObsPreference();
            $preference->setUser($user);
            $this->preferenceRepository->save($preference);
        }

        return $preference;
    }

    /**
     * Update user OBS preferences
     */
    public function updatePreferences(User $user, UpdateObsPreferenceRequest $request): UserObsPreference
    {
        $preference = $this->getOrCreatePreferences($user);

        // Update only provided fields
        if ($request->showTimerInSetup !== null) {
            $preference->setShowTimerInSetup($request->showTimerInSetup);
        }
        if ($request->showTimerInActive !== null) {
            $preference->setShowTimerInActive($request->showTimerInActive);
        }
        if ($request->showTimerInPaused !== null) {
            $preference->setShowTimerInPaused($request->showTimerInPaused);
        }
        if ($request->showTimerInCompleted !== null) {
            $preference->setShowTimerInCompleted($request->showTimerInCompleted);
        }

        if ($request->showRulesInSetup !== null) {
            $preference->setShowRulesInSetup($request->showRulesInSetup);
        }
        if ($request->showRulesInActive !== null) {
            $preference->setShowRulesInActive($request->showRulesInActive);
        }
        if ($request->showRulesInPaused !== null) {
            $preference->setShowRulesInPaused($request->showRulesInPaused);
        }
        if ($request->showRulesInCompleted !== null) {
            $preference->setShowRulesInCompleted($request->showRulesInCompleted);
        }

        if ($request->showStatusInSetup !== null) {
            $preference->setShowStatusInSetup($request->showStatusInSetup);
        }
        if ($request->showStatusInActive !== null) {
            $preference->setShowStatusInActive($request->showStatusInActive);
        }
        if ($request->showStatusInPaused !== null) {
            $preference->setShowStatusInPaused($request->showStatusInPaused);
        }
        if ($request->showStatusInCompleted !== null) {
            $preference->setShowStatusInCompleted($request->showStatusInCompleted);
        }

        if ($request->timerPosition !== null) {
            $preference->setTimerPosition($request->timerPosition);
        }

        if ($request->timerDesign !== null) {
            $preference->setTimerDesign($request->timerDesign);
        }
        if ($request->statusDesign !== null) {
            $preference->setStatusDesign($request->statusDesign);
        }
        if ($request->rulesDesign !== null) {
            $preference->setRulesDesign($request->rulesDesign);
        }

        if ($request->chromaKeyColor !== null) {
            $preference->setChromaKeyColor($request->chromaKeyColor);
        }

        $this->preferenceRepository->save($preference);

        return $preference;
    }
}

