<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 27/07/2019
 * Time: 13:41
 */

namespace App\Manager\Traits;


use App\Exception\MissingMethodException;

trait EntityGlobals
{
    /**
     * get
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    public function get(string $name, array $parameters = [])
    {
        $name = 'get'.ucfirst($name);
        if (method_exists($this, $name))
            return $this->$name($parameters);
        $name = 'is'.substr($name, 3);
        if (method_exists($this, $name))
            return $this->$name($parameters);
        throw new MissingMethodException($this, $name);
    }
}