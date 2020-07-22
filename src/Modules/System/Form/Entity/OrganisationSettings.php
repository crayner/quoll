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
 * Date: 22/07/2020
 * Time: 09:51
 */
namespace App\Modules\System\Form\Entity;

use App\Manager\AbstractEntity;
use App\Modules\System\Manager\SettingFactory;

/**
 * Class OrganisationSettings
 * @package App\Modules\System\Form\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 */
class OrganisationSettings extends AbstractEntity
{
    /**
     * getId
     * @return string|null
     * 22/07/2020 09:52
     */
    public function getId(): ?string
    {
        return null;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 22/07/2020 09:52
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * getOrganisationLogo
     * @return string
     * 22/07/2020 09:37
     */
    public static function getOrganisationLogo(): string
    {
        return SettingFactory::getSettingManager()->get('System', 'organisationLogo', '/build/static/logo.png');
    }

    /**
     * getOrganisationBackground
     * @return string
     * 22/07/2020 09:43
     */
    public static function getOrganisationBackground(): string
    {
        return SettingFactory::getSettingManager()->get('System', 'organisationBackground', '/build/static/backgroundPage.jpg');
    }
}
