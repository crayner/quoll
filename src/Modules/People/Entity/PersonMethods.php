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
 * Date: 27/07/2020
 * Time: 17:51
 */
namespace App\Modules\People\Entity;

/**
 * Trait PersonMethods
 * @package App\Modules\People\Entity
 */
trait PersonMethods
{
    /**
     * getFullName
     * @return string
     * 27/07/2020 10:31
     */
    public function getFullName(): string
    {
        return $this->getPerson()->formatName('Standard');
    }

    /**
     * getFullNameReversed
     * @return string
     * 27/07/2020 10:31
     */
    public function getFullNameReversed(): string
    {
        return $this->getPerson()->formatName('Reversed');
    }
}
