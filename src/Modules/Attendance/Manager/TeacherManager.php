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
 * Date: 3/10/2020
 * Time: 07:31
 */
namespace App\Modules\Attendance\Manager;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\School\Util\AcademicYearHelper;
use App\Provider\ProviderFactory;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class TeacherManager
 * @package App\Modules\Attendance\Manager
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TeacherManager
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private static AuthorizationCheckerInterface $checker;

    /**
     * TeacherManager constructor.
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(AuthorizationCheckerInterface $checker)
    {
        self::$checker = $checker;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public static function getChecker(): AuthorizationCheckerInterface
    {
        return self::$checker;
    }

    /**
     * getClassListQuery
     *
     * 3/10/2020 07:38
     * @return QueryBuilder
     */
    public static function getClassListQuery(): QueryBuilder
    {
        if (self::getChecker()->isGranted('ROLE_PRINCIPAL')) {
            return ProviderFactory::getRepository(CourseClass::class)->createQueryBuilder('cc')
                ->select(['cc','c'])
                ->orderBy('c.abbreviation', 'ASC')
                ->addOrderBy('cc.name', 'ASC')
                ->leftJoin('cc.course', 'c')
                ->where('c.academicYear = :current')
                ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                ;
        }
        if (self::getChecker()->isGranted('ROLE_HEAD_TEACHER')) {
            return ProviderFactory::getRepository(CourseClass::class)->createQueryBuilder('cc')
                ->select(['cc','c'])
                ->orderBy('c.abbreviation', 'ASC')
                ->addOrderBy('cc.name', 'ASC')
                ->leftJoin('cc.course', 'c')
                ->where('c.academicYear = :current')
                ->setParameter('current', AcademicYearHelper::getCurrentAcademicYear())
                ;
        }

    }
}
