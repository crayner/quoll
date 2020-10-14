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
 * Date: 14/10/2020
 * Time: 10:31
 */
namespace App\Modules\Timetable\Provider;

use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Entity\TimetablePeriodClass;
use App\Modules\Timetable\Util\TimetableDemoData;
use App\Provider\AbstractProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class TimetablePeriodClassProvider
 *
 * 14/10/2020 10:31
 * @package App\Modules\Timetable\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class TimetablePeriodClassProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = TimetablePeriodClass::class;

    /**
     * findByPeriod
     *
     * 14/10/2020 10:51
     * @param TimetablePeriod $period
     * @param string|null $idOnly
     * @param string|null $id
     * @return array
     */
    public function findByPeriod(TimetablePeriod $period, ?string $idOnly = null, ?string $id = null): array
    {
        if (is_null($idOnly)) return $this->getRepository()->findByPeriod($period, $idOnly);

        $result = [];
        foreach ($this->getRepository()->findByPeriod($period, $idOnly) as $item) {
            $result[] = $item['id'];
        }

        if ($id !== null && ($key = array_search($id, $result)) !== false) {
            unset($result[$key]);
        }
        return empty($result) ? [''] : $result;
    }


    /**
     * createTimetablePeriodClass
     *
     * 14/10/2020 14:20
     * @param array $content
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     */
    public static function createTimetablePeriodClass(array $content, LoggerInterface $logger, ValidatorInterface $validator)
    {
        return TimetableDemoData::createTimetablePeriodClass($content, $logger, $validator);
    }
}