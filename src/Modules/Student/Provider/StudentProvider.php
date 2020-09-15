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
 * Date: 20/07/2020
 * Time: 10:24
 */
namespace App\Modules\Student\Provider;

use App\Modules\Enrolment\Entity\CourseClassPerson;
use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;

/**
 * Class StudentProvider
 * @package App\Modules\Student\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = Student::class;


    /**
     * findClassEnrolmentBy
     *
     * 31/08/2020 09:14
     * @return array
     */
    public function getClassEnrolmentByRollGroupPaginationContent(): array
    {
        $result = $this->getRepository()->findClassEnrolmentByRollGroup();

        foreach ($this->getRepository(CourseClassPerson::class)->countClassEnrolmentByRollGroup() as $w) {
            $id = $w['id'];
            if (key_exists($id, $result)) {
                $result[$id]['classCount'] = $w['classCount'];
            }
        }

        return array_values($result);
    }

}
