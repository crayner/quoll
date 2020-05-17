<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 5/07/2019
 * Time: 11:35
 */

namespace App\Modules\School\Provider;

use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Exception\MissingClassException;
use App\Exception\MissingEntityException;
use App\Provider\AbstractProvider;
use App\Provider\ProviderFactory;
use App\Util\TranslationHelper;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\YearGroup;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class AcademicYearProvider
 * @package App\Modules\SchoolProvider\Provider
 */
class AcademicYearProvider extends AbstractProvider
{

    /**
     * @var string
     */
    protected $entityName = AcademicYear::class;

    /**
     * setCurrentAcademicYear
     * @param SessionInterface $session
     * @return object|null
     * @throws \Exception
     */
    public function setCurrentAcademicYear(SessionInterface $session): AcademicYear
    {
        $year = $this->getRepository()->findOneBy(['status' => 'Current']);

        //Check number of years returned.
        if (!$year instanceof AcademicYear) {
            if (!empty($this->getRepository()->findAll())) {
                throw new MissingEntityException(TranslationHelper::translate('Configuration Error: there is a problem accessing the current Academic Year from the database.', [], 'messages'));
            } else {
                $year = new AcademicYear();
                $year->setSequenceNumber(1)->setStatus('Current')->setFirstDay(new \DateTimeImmutable(date('Y') . '-01-01'))->setLastDay(new \DateTimeImmutable(date('Y') . '-12-31'))->setName(date('Y'));
                $this->persistFlush($year);
            }
        }
        
        $session->set('academicYear', $year);
        return $year;
    }

    /**
     * isAcademicYearOverlap
     * @param AcademicYear $year
     * @return bool
     */
    public function isAcademicYearOverlap(AcademicYear $year): bool
    {
        $result = $this->getRepository()->findAllByOverlap($year);
        return $result === [] ? false : true;
    }

    /**
     * selectAcademicYears
     * @param string $status
     * @param string $direction
     * @return array
     */
    public function selectAcademicYears(string $status = 'All', $direction = 'ASC'): array
    {
        $result = [];
        foreach($this->getRepository()->findByStatus($status, $direction) as $item)
        {
            $result[$item['name']] = $item['id'];
        }
        return $result;
    }

    /**
     * canDelete
     * @param AcademicYear $year
     * @return bool
     */
    public function canDelete(AcademicYear $year): bool
    {
        return $this->getRepository(StudentEnrolment::class)->countEnrolmentsByAcademicYear($year) === 0;
    }

    /**
     * getSelectList
     * @return array
     */
    public function getSelectList(): array
    {
        $result = [];
        foreach($this->getRepository()->findBy([], ['firstDay' => 'ASC', 'lastDay' => 'ASC']) as $year)
            $result[$year->getName()] = $year->getId();
        return $result;
    }

}