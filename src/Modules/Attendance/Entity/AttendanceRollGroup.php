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
 * Date: 17/10/2020
 * Time: 09:41
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Attendance\Validator\AttendanceLogTime;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceRollGroup
 *
 * 17/10/2020 09:42
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceRollGroupRepository")
 * @ORM\Table(name="AttendanceRollGroup",
 *     uniqueConstraints={
 *      @ORM\UniqueConstraint(name="roll_group_date_daily_time",columns={"roll_group","daily_time","date"})
 *     },
 *     indexes={
 *      @ORM\Index(name="roll_group",columns={"roll_group"})
 *     }
 * )
 * @UniqueEntity(ignoreNull=false,fields={"dailyTime","rollGroup","date"})
 * @AttendanceLogTime()
 */
class AttendanceRollGroup extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private ?string $id;

    /**
     * @var RollGroup|null
     * @ORM\ManyToOne(targetEntity="App\Modules\RollGroup\Entity\RollGroup")
     * @ORM\JoinColumn(name="rollGroup",nullable=false,name="roll_group")
     * @Assert\NotBlank()
     */
    private ?RollGroup $rollGroup;

    /**
     * @var string
     * @ORM\Column(length=32,nullable=false,name="daily_time",options={"default": "all_day"})
     */
    private string $dailyTime = 'all_day';

    /**
     * @var DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=false)
     */
    private ?DateTimeImmutable $date;

    /**
     * @var ArrayCollection|null
     */
    private ?ArrayCollection $recorderLogs;

    /**
     * AttendanceRollGroup constructor.
     *
     * @param RollGroup|null $rollGroup
     * @param string $dailyTime
     * @param DateTimeImmutable|null $date
     */
    public function __construct(?RollGroup $rollGroup, ?DateTimeImmutable $date, string $dailyTime = 'all_day')
    {
        $this->setRollGroup($rollGroup)
            ->setDailyTime($dailyTime)
            ->setDate($date);
    }
    
    /**
     * Id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * Id
     *
     * @param string|null $id
     * @return AttendanceRollGroup
     */
    public function setId(?string $id): AttendanceRollGroup
    {
        $this->id = $id;
        return $this;
    }

    /**
     * RollGroup
     *
     * @return RollGroup|null
     */
    public function getRollGroup(): ?RollGroup
    {
        return isset($this->rollGroup) ? $this->rollGroup : null;
    }

    /**
     * RollGroup
     *
     * @param RollGroup|null $rollGroup
     * @return AttendanceRollGroup
     */
    public function setRollGroup(?RollGroup $rollGroup): AttendanceRollGroup
    {
        $this->rollGroup = $rollGroup;
        return $this;
    }

    /**
     * DailyTime
     *
     * @return string|null
     */
    public function getDailyTime(): ?string
    {
        return $this->dailyTime = isset($this->dailyTime) ? $this->dailyTime : null;
    }

    /**
     * DailyTime
     *
     * @param string|null $dailyTime
     * @return AttendanceRollGroup
     */
    public function setDailyTime(?string $dailyTime): AttendanceRollGroup
    {
        $this->dailyTime = $dailyTime;
        return $this;
    }

    /**
     * Date
     *
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return isset($this->date) ? $this->date : null;
    }

    /**
     * Date
     *
     * @param DateTimeImmutable|null $date
     * @return AttendanceRollGroup
     */
    public function setDate(?DateTimeImmutable $date): AttendanceRollGroup
    {
        $this->date = $date;
        return $this;
    }

    /**
     * getRecorderLogs
     *
     * 27/10/2020 09:29
     * @return ArrayCollection
     */
    public function getRecorderLogs(): ArrayCollection
    {
        if (!isset($this->recorderLogs)) {
            $this->recorderLogs = new ArrayCollection(ProviderFactory::getRepository(AttendanceRecorderLog::class)->findBy(['logKey' => 'Roll Group', 'logId' => $this->getId()],['recordedOn' => 'ASC']));
        }
        return $this->recorderLogs;
    }

    /**
     * toArray
     *
     * 17/10/2020 10:09
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

}
