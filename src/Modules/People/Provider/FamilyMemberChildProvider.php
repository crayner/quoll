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
 * Date: 12/05/2020
 * Time: 08:55
 */
namespace App\Modules\People\Provider;

use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\FamilyMemberChild;
use App\Modules\People\Entity\Person;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;

/**
 * Class FamilyMemberChildProvider
 * @package App\Modules\People\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class FamilyMemberChildProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = FamilyMemberChild::class;

    /**
     * getStudentsOfParent
     * @param Person $parent
     * @return array
     * 19/06/2020 09:33
     */
    public function getStudentsOfParent(Person $parent): array
    {
        $result = ProviderFactory::getRepository(FamilyMemberAdult::class)->findFamiliesOfParent($parent, true);
        $families = [];
        foreach($result as $q=>$w) {
            if ($w->isChildDataAccess()) {
                $families[] = $w->getFamily();
            }
        }
        $students = [];
        foreach($families as $family) {
            $students = array_merge($students, $family->getStudents()->toArray());
        }

        return $students;
    }

}