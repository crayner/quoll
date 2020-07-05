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
 * Date: 9/12/2019
 * Time: 12:05
 */

namespace App\Modules\Student\Repository;

use App\Modules\Student\Entity\StudentNoteCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StudentNoteCategoryCategoryRepository
 * @package App\Modules\Student\Repository
 */
class StudentNoteCategoryRepository extends ServiceEntityRepository
{
    /**
     * StudentNoteCategoryRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StudentNoteCategory::class);
    }
}
