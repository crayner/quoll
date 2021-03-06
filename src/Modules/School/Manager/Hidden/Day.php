<?php
namespace App\Modules\School\Manager\Hidden;

use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\DaysOfWeek;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Manager\SettingManager;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Day
 * @package App\Modules\School\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class Day
{
	/**
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @var array
	 */
	protected $parameters;

	/**
	 * @var SettingManager
	 */
	protected $settingManager;

	/**
	 * @var int
	 */
	private $firstDayofWeek;

	/**
	 * @var int
	 */
	private $lastDayofWeek;

	/**
	 * @var int|null
	 */
	private $weekNumber = null;

	/**
	 * @var bool
	 */
	private $termBreak = false;

	/**
	 * @var  bool
	 */
	private $closed;

	/**
	 * @var  bool
	 */
	private $special;

	/**
	 * @var null
	 */
	private $prompt;

    /**
     * @var string
     */
    private $dayLong;

    /**
     * @var string
     */
    private $dayShort;

    /**
     * @var I18n
     */
    private $locale;

    /**
     * @var CalendarDisplayManager
     */
    private $manager;

    /**
     * @var string
     */
    private $name;

    /**
     * Day constructor.
     * @param DateTimeImmutable|null $date
     * @param int|null $weeks
     * @param CalendarDisplayManager|null $manager
     */
	public function __construct(?DateTimeImmutable $date = null, ?int $weeks = null, CalendarDisplayManager $manager = null)
	{
		$this->settingManager = SettingFactory::getSettingManager();
		$this->parameters     = [];
		$this->manager = $manager;
		if ($date instanceof DateTimeImmutable) {
            $this->date = clone $date;
            $this->day = $date->format($this->getLocale()->getDateFormatPHP());
            $this->dayLong = $date->format($this->getLocale()->getDateFormatPHP());
            $this->dayShort = $date->format($this->getLocale()->getDateFormatPHP());
            $this->firstDayofWeek = $this->getManager()->getFirstDayofWeek();
            $this->lastDayofWeek = $this->getManager()->getLastDayofWeek();
        }
        $this->special = false;
        $this->closed = false;
		$this->setWeekNumber($weeks);
	}

	/**
	 * @param null|int $weekNumber
	 *
	 * @return Week
	 */
	public function setWeekNumber(?int $weekNumber): Day
	{
		$this->weekNumber = $weekNumber;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getWeekNumber(): int
	{
		return $this->weekNumber;
	}

    /**
     * getDate
     *
     * @return \DateTime
     */
	public function getDate()
	{
		return $this->date;
	}

    /**
     * getNumber
     *
     * @return string
     */
	public function getNumber()
	{
		return $this->date->format('j');
	}

    /**
     * isFirstInWeek
     *
     * @return bool
     */
	public function isFirstInWeek()
	{
		return $this->date->format('N') == $this->firstDayofWeek;
	}

    /**
     * isLastInWeek
     *
     * @return bool
     */
	public function isLastInWeek()
	{
		return $this->date->format('N') == $this->lastDayofWeek;
	}

    /**
     * isInWeek
     *
     * @param Week $week
     * @return bool
     */
	public function isInWeek(Week $week)
	{
		return $this->date->format('W') == $week->getNumber();
	}

    /**
     * isInMonth
     *
     * @param Month $month
     * @return bool
     */
	public function isInMonth(Month $month)
	{
		return (($this->date->format('n') == $month->getNumber())
			&& ($this->date->format('Y') == $month->getYear()));
	}

    /**
     * isInYear
     *
     * 30/08/2020 14:04
     * @param string $year
     * @return bool
     */
	public function isInYear(string $year)
	{
		return $this->date->format('Y') == $year;
	}

    /**
     * setParameter
     *
     * 30/08/2020 14:04
     * @param $key
     * @param $value
     */
	public function setParameter($key, $value)
	{
		$this->parameters[$key] = $value;
	}

    /**
     * getParameter
     *
     * 30/08/2020 14:04
     * @param $key
     * @return mixed|null
     */
	public function getParameter($key)
	{
		return key_exists($key, $this->parameters) ? $this->parameters[$key] : null;
	}

    /**
     * @var bool
     */
	private bool $schoolDay;

    /**
     * isSchoolDay
     *
     * @return bool
     */
	public function isSchoolDay(): bool
	{
	    if (isset($this->schoolDay))
	        return $this->schoolDay;
	    if (null === $this->getDate())
	        return $this->schoolDay = true;
	    $dayOfWeek = $this->getDate()->format('N');
	    $day = null;
	    foreach ($this->getDaysOfWeek() as $day) {
	        if ($day->getSortOrder() === $dayOfWeek)
	            break;
        }

		return $this->schoolDay = $day ? $day->isSchoolDay() : false;
	}

	/**
	 * @param bool $schoolDay
	 *
	 * @return Day
	 */
	public function setSchoolDay(bool $schoolDay): Day
	{
		$this->schoolDay = $schoolDay;

		return $this;
	}

    /**
     * isTermBreak
     *
     * @return bool
     */
	public function isTermBreak(): bool
	{
		return $this->termBreak ? true : false ;
	}

    /**
     * setTermBreak
     *
     * @param bool $termBreak
     * @return Day
     */
	public function setTermBreak(bool $termBreak): Day
	{
		$this->termBreak = (bool) $termBreak;

		return $this;
	}

    /**
     * isClosed
     *
     * @return bool
     */
	public function isClosed(): bool
	{
		return $this->closed ? true : false ;
	}

    /**
     * setClosed
     *
     * @param bool $value
     * @param string $prompt
     */
	public function setClosed(bool $value, string $prompt)
	{
		$this->closed = (bool) $value;
		$this->prompt = $prompt;
	}

    /**
     * isSpecial
     * @return bool
     */
	public function isSpecial(): bool
	{
		return $this->special ? true : false ;
	}

    /**
     * setSpecial
     *
     * @param bool $value
     * @param string $prompt
     */
	public function setSpecial(bool $value, string $prompt)
	{
		$this->special = (bool) $value;
		$this->prompt  = $prompt;
	}

    /**
     * getPrompt
     *
     * @return null|string
     */
	public function getPrompt(): ?string
	{
		return $this->prompt;
	}

    /**
     * getFirstDayofWeek
     *
     * @return int
     */
    public function getFirstDayofWeek(): int
    {
        return $this->firstDayofWeek;
    }

    /**
     * getDaysOfWeek
     *
     * 30/08/2020 13:34
     * @return ArrayCollection
     */
    public function getDaysOfWeek(): ArrayCollection
    {
        return $this->getManager()->getDaysOfWeek();
    }

    /**
     * @return SettingManager
     */
    public function getSettingManager(): SettingManager
    {
        return $this->settingManager;
    }

    /**
     * deSerialise
     *
     * @param string $data
     * @return object
     */
    public function deSerialise(string $data): Day
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer('Y-m-d H:i:s'), new ObjectNormalizer()];
        $serialiser = new Serializer($normalizers, $encoders);

        $serialiser->deserialize($data, Day::class, 'json');

        return $this;
    }

    /**
     * getDayLong
     *
     * @return string
     */
    public function getDayLong(): string
    {
        return $this->dayLong;
    }

    /**
     * getDayShort
     *
     * @return string
     */
    public function getDayShort(): string
    {
        return $this->dayShort;
    }

    /**
     * setDayLong
     *
     * @param string $dayLong
     * @return Day
     */
    public function setDayLong(string $dayLong): Day
    {
        $this->dayLong = $dayLong;
        return $this;
    }

    /**
     * setDayShort
     *
     * @param string $dayShort
     * @return Day
     */
    public function setDayShort(string $dayShort): Day
    {
        $this->dayShort = $dayShort;
        return $this;
    }

    /**
     * @return CalendarDisplayManager
     */
    public function getManager(): CalendarDisplayManager
    {
        return $this->manager;
    }

    /**
     * getLocale
     * @return Locale
     */
    private function getLocale()
    {
        return $this->getManager()->getLocale();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (null === $this->name)
        {
            if (null === $this->getDate())
                $this->name = uniqid();
            else
                $this->name = $this->getDate()->format('Ymd');
        }
        return $this->name;
    }

    /**
     * getLabel
     * @return string
     */
    public function getLabel(): string
    {
        if (null === $this->getDate()) return '';

        return $this->getDate()->format('j');
    }

    /**
     * isEmpty
     * @return bool
     */
    public function isEmpty(): bool
    {
        return null === $this->getDate();
    }
}
