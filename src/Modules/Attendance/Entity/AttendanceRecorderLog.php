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
use App\Util\StringHelper;
use App\Util\TranslationHelper;
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
 *      indexes={
 *          @ORM\Index(name="recorder",columns="recorder"),
 *          @ORM\Index(name="code",columns="code")
 *      }
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
     * @var AttendanceCode|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Attendance\Entity\AttendanceCode")
     * @ORM\JoinColumn(name="code",nullable=true)
     */
    private ?AttendanceCode $code;


    /**
     * @var string|null
     * @ORM\Column(length=31,nullable=true)
     */
    private ?string $context;

    /**
     * @var string|null
     * @ORM\Column(length=31,nullable=true)
     */
    private ?string $reason;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private ?string $comment;

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
     * getCode
     *
     * 28/10/2020 10:02
     * @return AttendanceCode|null
     */
    public function getCode(): ?AttendanceCode
    {
        return isset($this->code) ? $this->code : null;
    }

    /**
     * Code
     *
     * @param AttendanceCode|null $code
     * @return AttendanceRecorderLog
     */
    public function setCode(?AttendanceCode $code): AttendanceRecorderLog
    {
        $this->code = $code;
        return $this;
    }

    /**
     * getContext
     *
     * 28/10/2020 10:17
     * @return string|null
     */
    public function getContext(): ?string
    {
        return isset($this->context) ? $this->context : null;
    }

    /**
     * Context
     *
     * @param string|null $context
     * @return AttendanceRecorderLog
     */
    public function setContext(?string $context): AttendanceRecorderLog
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Reason
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return isset($this->reason) ? $this->reason : null;
    }

    /**
     * Reason
     *
     * @param string|null $reason
     * @return AttendanceRecorderLog
     */
    public function setReason(?string $reason): AttendanceRecorderLog
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Comment
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return isset($this->comment) ? $this->comment : null;
    }

    /**
     * Comment
     *
     * @param string|null $comment
     * @return AttendanceRecorderLog
     */
    public function setComment(?string $comment): AttendanceRecorderLog
    {
        $this->comment = $comment;
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
        return [
            'recorded_on' => $this->getRecordedOn()->format('d M @ H:i'),
            'direction' => $this->getCode() ? $this->getCode()->getDirection() : 'Out',
            'direction_translated' => '<strong>' . TranslationHelper::translate($this->getCode() ? $this->getCode()->getDirection() : 'In', [], 'Attendance') . '</strong>',
            'details' => ($this->getCode() ? $this->getCode()->getName() : 'Present') . ', ' . $this->getReason() . "<br />\n" . $this->getComment(),
            'context' => $this->getContext() ? TranslationHelper::translate('attendance_student.context.' . strtolower($this->getContext()), [], 'Attendance'): 'Roll Group',
            'recorder' => $this->getRecorder()->getFullName('Standard'),
            'id' => $this->getId(),
        ];
    }

}
