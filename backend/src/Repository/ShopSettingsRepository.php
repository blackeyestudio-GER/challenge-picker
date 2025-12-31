<?php

namespace App\Repository;

use App\Entity\ShopSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShopSettings>
 */
class ShopSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopSettings::class);
    }

    public function getSetting(string $key, string $default = ''): string
    {
        $setting = $this->findOneBy(['settingKey' => $key]);

        return $setting ? $setting->getSettingValue() : $default;
    }

    public function setSetting(string $key, string $value): void
    {
        $setting = $this->findOneBy(['settingKey' => $key]);

        if (!$setting) {
            $setting = new ShopSettings();
            $setting->setSettingKey($key);
        }

        $setting->setSettingValue($value);

        $entityManager = $this->getEntityManager();
        $entityManager->persist($setting);
        $entityManager->flush();
    }

    public function isShopEnabled(): bool
    {
        return $this->getSetting('shop_enabled', '1') === '1';
    }
}
