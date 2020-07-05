<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Medical\Repository;

use App\Modules\Medical\Entity\PersonMedicalCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PersonMedicalConditionRepository
 * @package App\Modules\Medical\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonMedicalConditionRepository extends ServiceEntityRepository
{
    /**
     * PersonMedicalConditionRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonMedicalCondition::class);
    }
}
