<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/05/2020
 * Time: 11:17
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class AddressRepository
 * @package App\Modules\People\Repository
 */
class AddressRepository extends ServiceEntityRepository
{
    /**
     * AddressRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /**
     * getSingleStringAddress
     * @param Address $address
     * @return array
     */
    public function getSingleStringAddress(Address $address): array
    {
        $query = $this->createQueryBuilder('a')
            ->select(["CONCAT(COALESCE(a.flatUnitDetails,''),COALESCE(a.propertyName,''),COALESCE(a.streetNumber,''),a.streetName,COALESCE(a.postCode,''),l.id) AS name"])
            ->leftJoin('a.locality', 'l')
            ->where('a.locality = :locality')
            ->setParameter('locality', $address->getLocality());
        if (intval($address->getId()) > 0) {
            return $query->andWhere('a.id != :address_id')
                ->setParameter('address_id', $address->getId())
                ->getQuery()
                ->getResult();
        } else {
            return $query->andWhere('a.id IS NOT NULL')
                ->getQuery()
                ->getResult();
        }
    }
}