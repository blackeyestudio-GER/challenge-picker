<?php

namespace App\DTO\Response\Obs;

use App\Entity\UserObsPreference;

class ObsPreferenceResponse
{
    public bool $success = true;
    public ObsPreferenceData $data;

    public static function fromEntity(UserObsPreference $preference): self
    {
        $response = new self();
        $response->data = ObsPreferenceData::fromEntity($preference);

        return $response;
    }
}

class ObsPreferenceData
{
    public bool $showTimerInSetup;
    public bool $showTimerInActive;
    public bool $showTimerInPaused;
    public bool $showTimerInCompleted;

    public bool $showStatusInSetup;
    public bool $showStatusInActive;
    public bool $showStatusInPaused;
    public bool $showStatusInCompleted;

    public string $timerPosition;
    public string $timerDesign;
    public string $statusDesign;
    public string $rulesDesign;
    public string $chromaKeyColor;

    public static function fromEntity(UserObsPreference $preference): self
    {
        $data = new self();
        $data->showTimerInSetup = $preference->isShowTimerInSetup();
        $data->showTimerInActive = $preference->isShowTimerInActive();
        $data->showTimerInPaused = $preference->isShowTimerInPaused();
        $data->showTimerInCompleted = $preference->isShowTimerInCompleted();

        $data->showStatusInSetup = $preference->isShowStatusInSetup();
        $data->showStatusInActive = $preference->isShowStatusInActive();
        $data->showStatusInPaused = $preference->isShowStatusInPaused();
        $data->showStatusInCompleted = $preference->isShowStatusInCompleted();

        $data->timerPosition = $preference->getTimerPosition();
        $data->timerDesign = $preference->getTimerDesign();
        $data->statusDesign = $preference->getStatusDesign();
        $data->rulesDesign = $preference->getRulesDesign();
        $data->chromaKeyColor = $preference->getChromaKeyColor();

        return $data;
    }
}

