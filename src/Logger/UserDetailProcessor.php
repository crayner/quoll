<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
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
use App\Modules\Security\Entity\SecurityUser;
use App\Modules\Security\Util\SecurityHelper;

/**
 * Class UErDetailProcessor
 * @package App\Logger
 */
class UserDetailProcessor
{
    /**
     * @var null|SecurityUser
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
        $record['extra']['user']['name'] = $this->getUsername();

        return $record;
    }

    /**
     * getUser
     * @return SecurityUser|null
     * 3/07/2020 09:12
     */
    public function getUser(): ?SecurityUser
    {
        return $this->user = $this->user ?: SecurityHelper::getCurrentUser();
    }

    /**
     * getUserId
     * @return string
     * 3/07/2020 09:14
     */
    public function getUserId(): string
    {
        return $this->getUser() ? substr($this->getUser()->getId(), 0, 12) . '...' : 'Anon.';
    }

    /**
     * getUsername
     * @return string
     * 3/07/2020 09:14
     */
    public function getUsername(): string
    {
        return $this->getUser() ? $this->getUser()->getPerson()->getFullName() : '';
    }
}
