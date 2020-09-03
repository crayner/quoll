<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 3/09/2020
 * Time: 08:48
 */
namespace App\Manager\Hidden;

/**
 * Class PaginationGroup
 * @package App\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PaginationGroup
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $contentKey;

    /**
     * PaginationGroup constructor.
     * @param string $name
     * @param string $contentKey
     */
    public function __construct(string $name = '', string $contentKey = '')
    {
        $this->setName($name)
            ->setContentKey($contentKey);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PaginationGroup
     */
    public function setName(string $name): PaginationGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentKey(): string
    {
        return $this->contentKey;
    }

    /**
     * @param string $contentKey
     * @return PaginationGroup
     */
    public function setContentKey(string $contentKey): PaginationGroup
    {
        $this->contentKey = $contentKey;
        return $this;
    }

    /**
     * toArray
     *
     * 3/09/2020 08:56
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'contentKey' => $this->getContentKey(),
        ];
    }
}
