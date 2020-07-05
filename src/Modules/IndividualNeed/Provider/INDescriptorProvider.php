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
 * Date: 18/01/2020
 * Time: 10:15
 */
namespace App\Modules\IndividualNeed\Provider;

use App\Modules\IndividualNeed\Entity\INArchive;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use App\Modules\IndividualNeed\Entity\INPersonDescriptor;
use App\Provider\AbstractProvider;

/**
 * Class INDescriptorProvider
 * @package App\Modules\IndividualNeed\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class INDescriptorProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = INDescriptor::class;

    /**
     * canDelete
     * @param INDescriptor $descriptor
     * @return bool
     * 9/06/2020 12:35
     */
    public function canDelete(INDescriptor $descriptor): bool
    {
        if ($this->getRepository(INPersonDescriptor::class)->countDescriptor($descriptor) !== 0)
            return false;
        if ($this->getRepository(INArchive::class)->countDescriptor($descriptor) !== 0)
            return false;
        return true;
    }
}