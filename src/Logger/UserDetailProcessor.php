<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/10/2019
 * Time: 09:50
 */

namespace App\Logger;

use App\Modules\People\Entity\Person;
use App\Modules\People\Util\UserHelper;

/**
 * Class UErDetailProcessor
 * @package App\Logger
 */
class UserDetailProcessor
{
    /**
     * @var null|Person
     */
    private $user;

    /**
     * __invoke
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['user']['id'] = $this->getUserId();
        $record['extra']['user']['name'] = $this->getUserName();

        return $record;
    }

    /**
     * @return Person|null
     */
    public function getUser(): ?Person
    {
        return $this->user = $this->user ?: UserHelper::getCurrentUser();
    }

    /**
     * getUserId
     * @return string
     */
    public function getUserId(): string
    {
        return $this->getUser() ? substr($this->getUser()->getId(), 0, 12) . '...' : 'Anon.';
    }

    /**
     * getUserId
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUser() ? $this->getUser()->formatName(['style' => 'long', 'preferredName' => true]) : '';
    }
}
