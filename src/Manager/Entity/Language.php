<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 11/04/2020
 * Time: 09:26
 */

namespace App\Manager\Entity;


class Language
{
    /**
     * @var string|null
     */
    private $code = 'en_GB';

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Code.
     *
     * @param string|null $code
     * @return Language
     */
    public function setCode(?string $code): Language
    {
        $this->code = $code;
        return $this;
    }
}