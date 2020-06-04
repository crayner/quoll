<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Activity\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\Finance\Entity\FinanceInvoice;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ActivityStudentRepository
 * @package App\Modules\Activity\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Activity\Repository\ActivityStudentRepository")
 * @ORM\Table(name="ActivityStudent",
 *     indexes={
 *         @ORM\Index(name="activity", columns={"activity"}),
 *         @ORM\Index(name="person", columns={"person"}),
 *         @ORM\Index(name="invoice", columns={"invoice"}),
 *         @ORM\Index(name="backup_activity", columns={"backup_activity"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="activity_person", columns={"person","activity"})}
 * )
 * @UniqueEntity({"person","activity"})
 * @ORM\HasLifecycleCallbacks()
 */
class ActivityStudent extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    use BooleanList;

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Activity|null
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="students")
     * @ORM\JoinColumn(name="activity",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $activity;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id", nullable=false)
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string
     * @ORM\Column(length=12, options={"default": "Pending"})
     * @Assert\Choice(callback="getStatusList")
     */
    private $status = 'Pending';

    /**
     * @var array
     */
    private static $statusList = ['Accepted','Pending','Waiting List','Not Accepted'];

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable",name="created_on")
     */
    private $createdOn;

    /**
     * @var Activity|null
     * @ORM\ManyToOne(targetEntity="Activity")
     * @ORM\JoinColumn(name="backup_activity", referencedColumnName="id")
     */
    private $backupActivity;

    /**
     * @var string
     * @ORM\Column(length=1, name="invoice_generated", options={"default": "N"})
     */
    private $invoiceGenerated = 'N';

    /**
     * @var FinanceInvoice|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Finance\Entity\FinanceInvoice")
     * @ORM\JoinColumn(name="invoice",referencedColumnName="id")
     */
    private $invoice;

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ActivityStudent
     */
    public function setId(?string $id): ActivityStudent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Activity|null
     */
    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    /**
     * @param Activity|null $activity
     * @return ActivityStudent
     */
    public function setActivity(?Activity $activity): ActivityStudent
    {
        $this->activity = $activity;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     * @return ActivityStudent
     */
    public function setPerson(?Person $person): ActivityStudent
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ActivityStudent
     */
    public function setStatus(string $status): ActivityStudent
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Pending';
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->createdOn;
    }

    /**
     * setCreatedOn
     * @param \DateTimeImmutable|null $createdOn
     * @return ActivityStudent
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function setCreatedOn(?\DateTimeImmutable $createdOn = null): ActivityStudent
    {
        $this->createdOn = $createdOn ?: new \DateTimeImmutable();
        return $this;
    }

    /**
     * @return Activity|null
     */
    public function getBackupActivity(): ?Activity
    {
        return $this->backupActivity;
    }

    /**
     * @param Activity|null $backupActivity
     * @return ActivityStudent
     */
    public function setBackupActivity(?Activity $backupActivity): ActivityStudent
    {
        $this->backupActivity = $backupActivity;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceGenerated(): string
    {
        return $this->invoiceGenerated;
    }

    /**
     * @param string $invoiceGenerated
     * @return ActivityStudent
     */
    public function setInvoiceGenerated(string $invoiceGenerated): ActivityStudent
    {
        $this->invoiceGenerated = $this->checkBoolean($invoiceGenerated, 'N');
        return $this;
    }

    /**
     * @return FinanceInvoice|null
     */
    public function getInvoice(): ?FinanceInvoice
    {
        return $this->invoice;
    }

    /**
     * @param FinanceInvoice|null $invoice
     * @return ActivityStudent
     */
    public function setInvoice(?FinanceInvoice $invoice): ActivityStudent
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array
     * 3/06/2020 16:48
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__ActivityStudent` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `activity` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `backup_activity` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `invoice` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `status` varchar(12) NOT NULL DEFAULT 'Pending',
                    `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `invoice_generated` varchar(1) NOT NULL DEFAULT 'N',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `activity_person` (`person`,`activity`),
                    KEY `activity` (`activity`),
                    KEY `person` (`person`),
                    KEY `invoice` (`invoice`),
                    KEY `backup_activity` (`backup_activity`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 3/06/2020 16:48
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__ActivityStudent`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`invoice`) REFERENCES `__prefix__FinanceInvoice` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`activity`) REFERENCES `__prefix__Activity` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`backup_activity`) REFERENCES `__prefix__Activity` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 3/06/2020 16:48
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}