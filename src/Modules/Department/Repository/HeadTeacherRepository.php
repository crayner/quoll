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
 * Time: 09:22
 */
namespace App\Modules\Department\Repository;

use App\Modules\Department\Entity\HeadTeacher;
use App\Modules\People\Manager\PersonNameManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class HeadTeacherRepository
 * @package App\Modules\Department\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class HeadTeacherRepository extends ServiceEntityRepository
{
    /**
     * DepartmentStaffRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HeadTeacher::class);
    }

    /**
     * findHeadTeacherPaginationContent
     *
     * 3/10/2020 14:40
     * @return array
     */
    public function findHeadTeacherPaginationContent(): array
    {
        return $this->createQueryBuilder('t')
            ->select(
                [
                    't.id',
                    "CONCAT(".PersonNameManager::formatNameQuery('p', 'Staff','Reversed').") AS name",
                    'COUNT(cc.id) AS classes',
                    't.title',
                ]
            )
            ->leftJoin('t.teacher', 's')
            ->leftJoin('s.person', 'p')
            ->leftJoin('t.classes', 'cc')
            ->orderBy('name','ASC')
            ->groupBy('t.id')
            ->getQuery()
            ->getResult();
    }
}