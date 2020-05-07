<?php
/**
 * Created by PhpStorm.
 *
 * quoll
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
        dump($address);
        return $this->createQueryBuilder('a')
            ->join('a.locality', 'l')
            ->select(['a','l'])
            ->where('a.id != :address_id')
            ->setParameter('address_id', $address->getId())
            ->andWhere('a.locality = :locality')
            ->setParameter('locality', $address->getLocality())
            ->getQuery()
            ->getResult();
    }
}