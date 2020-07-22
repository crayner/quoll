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
 * Date: 19/07/2020
 * Time: 15:20
 */
namespace App\Modules\People\Entity\Additional;

use App\Manager\EntityInterface;
use App\Modules\School\Entity\House;
use App\Util\ImageHelper;
use App\Validator\ReactImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait SchoolCommonFields
 * @package App\Modules\People\Entity
 */
trait SchoolCommonFields
{
    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $dateEnd;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $viewCalendarPersonal = true;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $calendarFeedPersonal;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $viewCalendarSchool;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     * @ReactImage(
     *     mimeTypes = {"image/jpg","image/jpeg","image/png","image/gif"},
     *     maxSize = "1536k",
     *     maxRatio = 1.777,
     *     minRatio = 1.25,
     * )
     * 16/9, 800/640
     */
    private $personalBackground;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",nullable=true)
     */
    private $messengerLastBubble;

    /**
     * @var House|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\House")
     * @ORM\JoinColumn(nullable=true, name="house", referencedColumnName="id")
     */
    private $house;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $lockerNumber;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $receiveNotificationEmails = true;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateStart(): ?\DateTimeImmutable
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTimeImmutable|null $dateStart
     * @return $this|EntityInterface
     */
    public function setDateStart(?\DateTimeImmutable $dateStart): EntityInterface
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDateEnd(): ?\DateTimeImmutable
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTimeImmutable|null $dateEnd
     * @return $this|EntityInterface
     */
    public function setDateEnd(?\DateTimeImmutable $dateEnd): EntityInterface
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isViewCalendarPersonal(): bool
    {
        return (bool)$this->viewCalendarPersonal;
    }

    /**
     * @param bool|null $viewCalendarPersonal
     * @return $this|EntityInterface
     */
    public function setViewCalendarPersonal(?bool $viewCalendarPersonal): EntityInterface
    {
        $this->viewCalendarPersonal = (bool)$viewCalendarPersonal;
        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarFeedPersonal(): ?string
    {
        return $this->calendarFeedPersonal;
    }

    /**
     * @param string|null $calendarFeedPersonal
     * @return $this|EntityInterface
     */
    public function setCalendarFeedPersonal(?string $calendarFeedPersonal): EntityInterface
    {
        $this->calendarFeedPersonal = $calendarFeedPersonal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isViewCalendarSchool(): bool
    {
        return (bool)$this->viewCalendarSchool;
    }

    /**
     * @param bool|null $viewCalendarSchool
     * @return $this|EntityInterface
     */
    public function setViewCalendarSchool(?bool $viewCalendarSchool): EntityInterface
    {
        $this->viewCalendarSchool = (bool)$viewCalendarSchool;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPersonalBackground(): ?string
    {
        if ($this->personalBackground === null || ImageHelper::isFileInPublic($this->personalBackground))
            return $this->personalBackground;
        return null;
    }

    /**
     * @param string|null $personalBackground
     * @return $this|EntityInterface
     */
    public function setPersonalBackground(?string $personalBackground): EntityInterface
    {
        $this->personalBackground = $personalBackground;
        return $this;
    }

    /**
     * removePersonalBackground
     * @return $this|EntityInterface
     * 19/07/2020 17:04
     */
    public function removePersonalBackground(): EntityInterface
    {
        $this->personalBackground = null;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getMessengerLastBubble(): ?\DateTimeImmutable
    {
        return $this->messengerLastBubble;
    }

    /**
     * @param \DateTimeImmutable|null $messengerLastBubble
     * @return $this|EntityInterface
     */
    public function setMessengerLastBubble(?\DateTimeImmutable $messengerLastBubble): EntityInterface
    {
        $this->messengerLastBubble = $messengerLastBubble;
        return $this;
    }

    /**
     * @return House|null
     */
    public function getHouse(): ?House
    {
        return $this->house;
    }

    /**
     * @param House|null $house
     * @return $this|EntityInterface
     */
    public function setHouse(?House $house): EntityInterface
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLockerNumber(): ?string
    {
        return $this->lockerNumber;
    }

    /**
     * @param string|null $lockerNumber
     * @return $this|EntityInterface
     */
    public function setLockerNumber(?string $lockerNumber): EntityInterface
    {
        $this->lockerNumber = $lockerNumber;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isReceiveNotificationEmails(): bool
    {
        return (bool)$this->receiveNotificationEmails;
    }

    /**
     * setReceiveNotificationEmails
     * @param bool|null $receiveNotificationEmails
     * @return $this|EntityInterface
     * 19/07/2020 16:34
     */
    public function setReceiveNotificationEmails(?bool $receiveNotificationEmails): EntityInterface
    {
        $this->receiveNotificationEmails = (bool)$receiveNotificationEmails;
        return $this;
    }

}
