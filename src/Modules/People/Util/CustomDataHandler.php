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
 * Date: 28/08/2020
 * Time: 11:32
 */
namespace App\Modules\People\Util;

use App\Modules\People\Entity\CustomField;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class CustomDataHandler
 * @package App\Modules\People\Util
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CustomDataHandler
{
    /**
     * @var array 
     */
    private static array $fields = [];

    /**
     * findCustomFields
     *
     * 28/08/2020 11:51
     * @param string $category
     * @return ArrayCollection
     */
    public static function findCustomFields(string $category): ArrayCollection
    {
        if (key_exists($category,self::$fields)) return self::$fields[$category];
        
        return self::$fields[$category] = new ArrayCollection(ProviderFactory::getRepository(CustomField::class)->findByCategory($category));
    }
}
