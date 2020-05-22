<?php
/**
 * Created by PhpStorm.
 *
* Quoll
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
     * getId
     * @return string|int|null
     */
    public function getId();

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array;

    /**
     * create
     * @return array
     */
    public function create(): array;

    /**
     * foreignConstraints
     * @return string
     */
    public function foreignConstraints(): string;

    /**
     * coreData
     * @return string
     */
    public function coreData(): string;

    /**
     * getVersion
     * @return string
     */
    public static function getVersion(): string;
}