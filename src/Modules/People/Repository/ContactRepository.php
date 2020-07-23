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
 * Date: 2/07/2020
 * Time: 08:47
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Contact;
use App\Modules\People\Entity\Locality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ContactRepository
 * @package App\Modules\People\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ContactRepository extends ServiceEntityRepository
{
    /**
     * ContactRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * findPhoneList
     * @return array
     * 22/07/2020 15:39
     */
    public function findPhoneList(): array
    {
        return $this->createQueryBuilder('c', 'c.id')
            ->where('c.personalPhone IS NOT NULL')
            ->select(['ph.id'])
            ->join('c.personalPhone', 'ph')
            ->getQuery()
            ->getResult();
    }

    /**
     * countLocalityUse
     * @param Locality $locality
     * @return int
     * 22/07/2020 15:52
     */
    public function countLocalityUse(Locality $locality): int
    {
        try {
            return $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->leftJoin('c.physicalAddress', 'a')
                ->leftJoin('c.postalAddress', 'pa')
                ->where('a.locality = :locality')
                ->orWhere('pa.locality = :locality')
                ->setParameter('locality', $locality)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }
}
