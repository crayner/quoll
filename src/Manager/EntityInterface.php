<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 16/12/2018
 * Time: 16:49
 */
namespace App\Manager;

/**
 * Interface EntityInterface
 * @package App\Manager
 */
interface EntityInterface
{
    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array;

    /**
     * coreData
     * @return string
     */
    public function coreData(): array;

    /**
     * getVersion
     * @return string
     */
    public static function getVersion(): string;
}