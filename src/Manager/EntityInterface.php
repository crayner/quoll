<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
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
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array;

    /**
     * create
     * @return string
     */
    public function create(): string;

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
}