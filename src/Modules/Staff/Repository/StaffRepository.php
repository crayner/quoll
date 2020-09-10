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
namespace App\Modules\Staff\Repository;

use App\Modules\People\Entity\Person;
use App\Modules\People\Repository\PersonRepository;
use App\Modules\School\Entity\House;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use App\Util\TranslationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StaffRepository
 * @package App\Modules\Staff\Repository
 */
class StaffRepository extends ServiceEntityRepository
{
    /**
     * @var array|null
     */
    private $params;

    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Staff::class);
    }

    /**
     * findOneByPersonOrCreate
     * @param Person $person
     * @return Staff
     * 23/06/2020 10:04
     */
    public function findOneByPersonOrCreate(Person $person): Staff
    {
        return $this->findOneByPerson($person) ?: new Staff($person);
    }

    /**
     * countInHouse
     * @param House $house
     * @return int
     * 16/07/2020 10:25
     */
    public function countInHouse(House $house): int
    {
        try {
            return $this->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.house = :house')
                ->setParameter('house', $house)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * getStaffQueryBuilder
     * @param string $status
     * @return QueryBuilder
     * 17/06/2020 11:33
     */
    public function getStaffQuery(string $status = 'Full'): QueryBuilder
    {
        $today = new \DateTimeImmutable(date('Y-m-d'));
        $this->setParams([])
            ->addParam('status', $status)
            ->addParam('today', $today);

        return $this->getAllStaffQuery()
            ->andWhere('p.status = :status')
            ->andWhere('(s.dateStart IS NULL OR s.dateStart <= :today)')
            ->andWhere('(s.dateEnd IS NULL OR s.dateEnd >= :today)')
            ->setParameters($this->getParams())
            ;
    }

    /**
     * getAllStaffQuery
     *
     * 24/08/2020 11:39
     * @return QueryBuilder
     */
    public function getAllStaffQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->select(['s','p','pd','c'])
            ->leftJoin('s.person', 'p')
            ->leftJoin('p.personalDocumentation', 'pd')
            ->leftJoin('p.contact', 'c')
            ->orderBy('p.surname')
            ->addOrderBy('p.firstName')
            ;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        if (null === $this->params) {
            $this->params = [];
        }

        return $this->params;
    }

    /**
     * setParams
     * @param array|null $params
     * @return $this
     * 27/07/2020 08:42
     */
    public function setParams(?array $params): StaffRepository
    {
        $this->params = $params;
        return $this;
    }

    /**
     * addParam
     * @param $name
     * @param $value
     * @return $this
     * 27/07/2020 08:46
     */
    public function addParam($name, $value): StaffRepository
    {
        $this->getParams();

        $this->params[$name] = $value;

        return $this;
    }

    /**
     * findCurrentStaffAsArray
     * @return int|mixed|string
     * 27/07/2020 08:28
     */
    public function findCurrentStaffAsArray(): array
    {
        $staff = TranslationHelper::translate('Staff', [], 'Staff');
        return $this->getStaffQuery('Full')
            ->select(["CONCAT(p.surname,p.firstName,p.preferredName) AS data", "'".$staff."' AS type", "CONCAT('".$staff.": ',p.surname,': ',p.firstName,' (',p.preferredName,')') AS label","COALESCE(d.personalImage,'/build/static/DefaultPerson.png') AS photo",'p.id AS value'])
            ->leftJoin('p.personalDocumentation', 'd')
            ->getQuery()
            ->getResult();
    }

    /**
     * mergeStaffIndividualEnrolmentPagination
     *
     * 10/09/2020 14:04
     * @return array
     */
    public function mergeStaffIndividualEnrolmentPagination(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(Person::class, 'p', 'p.id')
            ->select(['p.id', "'Staff' AS role", 's.type AS category',"'' AS rollGroup","'' AS yearGroup"])
            ->leftJoin('p.staff', 's')
            ->where('p.staff IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

}
