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
 * Date: 27/10/2020
 * Time: 08:25
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use App\Modules\Staff\Entity\Staff;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AttendanceRecorderLog
 *
 * 27/10/2020 08:25
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceRecorderLogRepository")
 * @ORM\Table(name="AttendanceRecorderLog",
 *      indexes={@ORM\Index(name="recorder",columns="recorder")}
 * )
 */
class AttendanceRecorderLog extends AbstractEntity
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
     * @var string|null
     * @ORM\Column(length=16,options={"default": "Student"})
     * @Assert\Choice(callback="getLogKeyList")
     */
    private ?string $logKey = 'Student';

    /**
     * @var array|string[]
     */
    private static array $logKeyList = [
        'Student',
        'Roll Group',
        'Course Class',
    ];

    /**
     * @var string|null
     * @ORM\Column(type="guid",nullable=false)
     * @Assert\NotBlank()
     */
    private ?string $logId;

    /**
     * @var Staff|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Staff\Entity\Staff")
     * @ORM\JoinColumn(name="recorder",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Staff $recorder;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",nullable=false)
     * @Assert\NotBlank()
     */
    private ?\DateTimeImmutable $recordedOn;

    /**
     * Id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id
     *
     * @param string|null $id
     * @return AttendanceRecorderLog
     */
    public function setId(?string $id): AttendanceRecorderLog
    {
        $this->id = $id;
        return $this;
    }

    /**
     * LogKey
     *
     * @return string|null
     */
    public function getLogKey(): ?string
    {
        return $this->logKey;
    }

    /**
     * LogKey
     *
     * @param string|null $logKey
     * @return AttendanceRecorderLog
     */
    public function setLogKey(?string $logKey): AttendanceRecorderLog
    {
        $this->logKey = in_array($logKey, self::getLogKeyList()) ? $logKey : 'Unknown';
        return $this;
    }

    /**
     * LogKeyList
     *
     * @return array|string[]
     */
    public static function getLogKeyList()
    {
        return self::$logKeyList;
    }

    /**
     * @param array|string[] $logKeyList
     */
    public static function setLogKeyList($logKeyList): void
    {
        self::$logKeyList = $logKeyList;
    }

    /**
     * LogId
     *
     * @return string|null
     */
    public function getLogId(): ?string
    {
        return $this->logId;
    }

    /**
     * LogId
     *
     * @param string|null $logId
     * @return AttendanceRecorderLog
     */
    public function setLogId(?string $logId): AttendanceRecorderLog
    {
        $this->logId = $logId;
        return $this;
    }

    /**
     * Recorder
     *
     * @return Staff|null
     */
    public function getRecorder(): ?Staff
    {
        return $this->recorder;
    }

    /**
     * Recorder
     *
     * @param Staff|null $recorder
     * @return AttendanceRecorderLog
     */
    public function setRecorder(?Staff $recorder): AttendanceRecorderLog
    {
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * RecordedOn
     *
     * @return \DateTimeImmutable|null
     */
    public function getRecordedOn(): ?\DateTimeImmutable
    {
        return $this->recordedOn;
    }

    /**
     * RecordedOn
     *
     * @param \DateTimeImmutable|null $recordedOn
     * @return AttendanceRecorderLog
     */
    public function setRecordedOn(?\DateTimeImmutable $recordedOn): AttendanceRecorderLog
    {
        $this->recordedOn = $recordedOn;
        return $this;
    }

    /**
     * toArray
     *
     * 27/10/2020 08:38
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

}
