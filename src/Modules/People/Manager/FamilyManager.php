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
 * Date: 10/12/2019
 * Time: 08:47
 */

namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyAdult;
use App\Modules\People\Entity\FamilyChild;
use App\Modules\People\Util\StudentHelper;
use App\Provider\ProviderFactory;
use App\Util\ImageHelper;
use App\Util\TranslationHelper;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Class FamilyManager
 * @package App\Modules\People\Manager
 */
class FamilyManager
{
    /**
     * @var array|null
     */
    private static $allAdults;

    /**
     * @var array|null
     */
    private static $allStudents;

    /**
     * findBySearch
     * @return array
     */
    public function findBySearch(): array
    {
        $result = ProviderFactory::getRepository(Family::class)->findBySearch();

        $familyList = [];
        foreach($result as $q=>$family)
            $familyList[] = $family['id'];

        self::$allAdults = ProviderFactory::getRepository(FamilyAdult::class)->findByFamilyList($familyList);
        self::$allStudents = ProviderFactory::getRepository(FamilyChild::class)->findByFamilyList($familyList);

        foreach($result as $q=>$family)
        {
            $family['adults'] = self::getAdultNames($family['id']);
            $family['children'] = self::getChildrenNames($family['id']);
            $result[$q] = $family;
        }
        return $result;
    }

    /**
     * getAdultNames
     * @param Family $family
     * @return string
     */
    public function getAdultNames($family): string
    {
        $result = '';
        if (is_array(self::$allAdults)) {
            foreach(self::$allAdults as $adult) {
                if ($adult['id'] < $family)
                    continue;
                if ($adult['id'] > $family)
                    break;
                $adult['personType'] = 'Parent';
                $result .= PersonNameManager::formatName($adult, ['style' => 'formal']) . "\n<br />";
            }
            return $result;
        }
        foreach (self::getAdults($family, true) as $adult) {
            $adult['personType'] = 'Parent';
            $result .= PersonNameManager::formatName($adult, ['style' => 'formal']) . "\n<br />";
        }
        return $result;
    }

    /**
     * getChildrenNames
     * @param Family $family
     * @return string
     */
    public function getChildrenNames($family): string
    {
        $result = '';
        if (is_array(self::$allStudents)) {
            foreach(self::$allStudents as $student) {
                if ($student['id'] < $family)
                    continue;
                if ($student['id'] > $family)
                    break;
                $student['personType'] = 'Student';
                $result .= PersonNameManager::formatName($student, ['style' => 'formal']) . "\n<br />";
            }
            return $result;
        }
        foreach (self::getChildren($family, true) as $student) {
            $student['personType'] = 'Student';
            $result .= PersonNameManager::formatName($student, ['style' => 'formal']) . "\n<br />";
        }
        return $result;
    }

    /**
     * getAdults
     * @param Family $family
     * @param bool $asArray
     * @return array
     */
    public static function getAdults(Family $family, bool $asArray = true): array
    {
        $result = ProviderFactory::getRepository(FamilyAdult::class)->findByFamily($family);
        if ($asArray)
            foreach($result as $q=>$adult)
                $result[$q] = $adult->toArray();
        return $result;
    }

    /**
     * getAdults
     * @param Family $family
     * @param bool $asArray
     * @return array
     */
    public static function getChildren($family, bool $asArray = true): array
    {
        $result = ProviderFactory::getRepository(FamilyChild::class)->findByFamily($family);
        if ($asArray)
            foreach($result as $q=>$child)
                $result[$q] = $child->toArray();
        return $result;
    }

    /**
     * deleteFamily
     * @param Family $family
     * @param FlashBagInterface $flashBag
     */
    public function deleteFamily(Family $family, FlashBagInterface $flashBag)
    {
        $adults = self::getAdults($family);
        $students = self::getChildren($family);
        $data = [];
        $data['status'] = ['success'];
        $data['errors'] = [];
        $provider = ProviderFactory::create(Family::class);
        foreach($adults as $adult)
            $data = $provider->remove($adult,$data, false);
        foreach($students as $student)
            $data = $provider->remove($student, $data, false);
        $data = $provider->remove($family, $data, true);

        $data['errors'] = array_unique($data['errors'], SORT_REGULAR);
        foreach($data['errors'] as $error)
            $flashBag->add($error['class'], $error['message']);
    }
}