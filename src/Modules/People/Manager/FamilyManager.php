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
 * Date: 10/12/2019
 * Time: 08:47
 */

namespace App\Modules\People\Manager;

use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Provider\ProviderFactory;
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
    public function getPaginationContent(): array
    {
        $result = ProviderFactory::getRepository(Family::class)->getPaginationContent();

        $familyList = [];
        foreach($result as $q=>$family)
            $familyList[] = $family['id'];

        self::$allAdults = ProviderFactory::getRepository(FamilyMemberCareGiver::class)->findByFamilyList($familyList);
        self::$allStudents = ProviderFactory::getRepository(FamilyMemberStudent::class)->findByFamilyList($familyList);

        foreach($result as $q=>$family)
        {
            $family['careGivers'] = self::getCareGiverNames($family['id']);
            $family['students'] = self::getStudentNames($family['id']);
            $result[$q] = $family;
        }
        return $result;
    }

    /**
     * getCareGiverNames
     * @param $family
     * @return string
     * 25/07/2020 08:12
     */
    public function getCareGiverNames($family, string $join = "<br />\n"): string
    {
        $result = [];
        if (is_array(self::$allAdults)) {
            foreach(self::$allAdults as $careGiver) {
                if ($careGiver['id'] < $family)
                    continue;
                if ($careGiver['id'] > $family)
                    break;
                $careGiver['personType'] = 'Care Giver';
                $result[] = $careGiver['fullName'];
            }
            return trim(implode($join, $result), $join);
        }
        foreach (self::getCareGivers($family, true) as $careGiver) {
            $careGiver['personType'] = 'Care Giver';
            $result[] = $careGiver['fullName'];
        }
        return trim(implode($join, $result), $join);
    }

    /**
     * getStudentsNames
     * @param $family
     * @return string
     * 24/07/2020 13:07
     */
    public function getStudentNames($family, string $join = "<br />\n"): string
    {
        $result = [];
        if (is_array(self::$allStudents)) {
            foreach(self::$allStudents as $student) {
                if ($student['id'] < $family)
                    continue;
                if ($student['id'] > $family)
                    break;
                $student['personType'] = 'Student';
                $result[] = $student['fullName'];
            }
            return trim(implode($join, $result), $join);
        }
        foreach (self::getStudents($family, true) as $student) {
            $student['personType'] = 'Student';
            $result[] = $student['fullName'];
        }
        return trim(implode($join, $result), $join);
    }

    /**
     * getCareGivers
     * @param Family $family
     * @param bool $asArray
     * @return array
     * 24/07/2020 13:00
     */
    public static function getCareGivers(Family $family, bool $asArray = true): array
    {
        return ProviderFactory::getRepository(FamilyMemberCareGiver::class)->findByFamily($family, $asArray);
    }

    /**
     * getStudents
     * @param Family $family
     * @param bool $asArray
     * @return array
     * 24/07/2020 14:36
     */
    public static function getStudents(Family $family, bool $asArray = true): array
    {
        return ProviderFactory::getRepository(FamilyMemberStudent::class)->findByFamily($family, $asArray);
    }

    /**
     * deleteFamily
     * @param Family $family
     * @param FlashBagInterface $flashBag
     */
    public function deleteFamily(Family $family, FlashBagInterface $flashBag)
    {
        $adults = self::getCareGivers($family);
        $students = self::getStudents($family);
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