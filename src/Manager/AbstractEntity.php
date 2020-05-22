<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 21/05/2020
 * Time: 16:03
 */
namespace App\Manager;

/**
 * Class AbstractEntity
 * @package App\Manager
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * getUpdates
     * @return array
     */
    public function getUpdates(): array
    {
        return [];
    }

    /**
     * coreData
     * @return string
     */
    public function coreData(): string
    {
        return '';
    }

    public function getUpdate(): array
    {
        return [];
    }

    /**
     * isUpdateRequired
     * @param \DateTimeImmutable|null $date
     * @return bool
     */
    public function isUpdateRequired(?\DateTimeImmutable $date): bool
    {
        return self::getVersion() < $date->format('Ymd') || null === $date;
    }
}