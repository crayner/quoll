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
namespace App\Modules\Finance\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use phpDocumentor\Reflection\Types\Collection;

/**
 * Class FinanceInvoice
 * @package App\Modules\Finance\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Finance\Repository\FinanceInvoiceRepository")
 * @ORM\Table(name="FinanceInvoice",
 *     indexes={@ORM\Index(name="academic_year",columns={"academic_year"}),
 *     @ORM\Index(name="billing_schedule",columns={"billing_schedule"}),
 *     @ORM\Index(name="payment",columns={"payment"}),
 *     @ORM\Index(name="creator",columns={"creator"}),
 *     @ORM\Index(name="updater",columns={"updater"}),
 *     @ORM\Index(name="invoicee",columns={"invoicee"})})
 * @ORM\HasLifecycleCallbacks()
 */
class FinanceInvoice extends AbstractEntity
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
     * @var AcademicYear|null
     * @ORM\ManyToOne(targetEntity="App\Modules\School\Entity\AcademicYear")
     * @ORM\JoinColumn(name="academic_year", referencedColumnName="id")
     */
    private $academicYear;

    /**
     * @var FinanceInvoicee|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Finance\Entity\FinanceInvoicee")
     * @ORM\JoinColumn(name="invoicee", referencedColumnName="id")
     */
    private $invoicee;

    /**
     * @var string
     * @ORM\Column(length=8,options={"default": "Family"})
     */
    private $invoiceTo = 'Family';

    /**
     * @var array
     */
    private static $invoiceToList = ['Family', 'Company'];

    /**
     * @var string
     * @ORM\Column(length=12, options={"default": "Ad Hoc"})
     */
    private $scheduleType = 'Ad Hoc';

    /**
     * @var array
     */
    private static $scheduleTypeList = ['Scheduled', 'Ad Hoc'];

    /**
     * @var string
     * @ORM\Column(length=1, options={"comment": "Has this invoice been separated from its schedule in FinanceBillingSchedule? Only applies to scheduled invoices. Separation takes place during invoice issueing.", "default": "N"})
     */
    private $separated = 'N';

    /**
     * @var FinanceBillingSchedule|null
     * @ORM\ManyToOne(targetEntity="App\Modules\Finance\Entity\FinanceBillingSchedule")
     * @ORM\JoinColumn(referencedColumnName="id",nullable=true,name="billing_schedule")
     */
    private $billingSchedule;

    /**
     * @var string
     * @ORM\Column(length=16, options={"default": "Pending"})
     */
    private $status = 'Pending';

    /**
     * @var array
     */
    private static $statusList = ['Pending','Issued','Paid','Paid - Partial','Cancelled','Refunded'];

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\Finance\Entity\FinanceFeeCategory")
     * @ORM\JoinTable(name="FinanceInvoiceFeeCategory",
     *      joinColumns={@ORM\JoinColumn(name="invoice",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fee_category",referencedColumnName="id")}
     *  )
     */
    private $feeCategories;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     */
    private $invoiceIssueDate;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $invoiceDueDate;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable")
     */
    private $paidDate;

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=13, scale=2, nullable=true, options={"comment": "The current running total amount paid to this invoice"})
     */
    private $paidAmount;

    /**
     * @var Payment|null
     * @ORM\ManyToOne(targetEntity="Payment")
     * @ORM\JoinColumn(referencedColumnName="id",nullable=true,name="payment")
     */
    private $payment;

    /**
     * @var integer|null
     * @ORM\Column(type="smallint",options={"default": "0"})
     */
    private $reminderCount = 0;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @var string|null
     * @ORM\Column(length=40)
     */
    private $key;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(referencedColumnName="id",name="creator")
     */
    private $creator;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdOn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(referencedColumnName="id",name="updater")
     */
    private $updater;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable")
     */
    private $updatedOn;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return FinanceInvoice
     */
    public function setId(?string $id): FinanceInvoice
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->academicYear;
    }

    /**
     * @param AcademicYear|null $academicYear
     * @return FinanceInvoice
     */
    public function setAcademicYear(?AcademicYear $academicYear): FinanceInvoice
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    /**
     * @return FinanceInvoicee|null
     */
    public function getInvoicee(): ?FinanceInvoicee
    {
        return $this->invoicee;
    }

    /**
     * setInvoicee
     * @param FinanceInvoicee|null $invoicee
     * @return $this
     * 3/06/2020 17:33
     */
    public function setInvoicee(?FinanceInvoicee $invoicee): FinanceInvoice
    {
        $this->invoicee = $invoicee;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceTo(): string
    {
        return $this->invoiceTo;
    }

    /**
     * @param string $invoiceTo
     * @return FinanceInvoice
     */
    public function setInvoiceTo(string $invoiceTo): FinanceInvoice
    {
        $this->invoiceTo = in_array($invoiceTo, self::getInvoiceToList()) ? $invoiceTo : 'Family';
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparated(): string
    {
        return $this->separated;
    }

    /**
     * @param string $separated
     * @return FinanceInvoice
     */
    public function setSeparated(string $separated): FinanceInvoice
    {
        $this->separated = self::checkBoolean($separated, null);
        return $this;
    }

    /**
     * @return FinanceBillingSchedule|null
     */
    public function getBillingSchedule(): ?FinanceBillingSchedule
    {
        return $this->billingSchedule;
    }

    /**
     * @param FinanceBillingSchedule|null $billingSchedule
     * @return FinanceInvoice
     */
    public function setBillingSchedule(?FinanceBillingSchedule $billingSchedule): FinanceInvoice
    {
        $this->billingSchedule = $billingSchedule;
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
     * @return FinanceInvoice
     */
    public function setStatus(string $status): FinanceInvoice
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Pending';
        return $this;
    }

    /**
     * @return Collection
     */
    public function getFeeCategories(): Collection
    {
        if ($this->feeCategories === null) {
            $this->feeCategories = new ArrayCollection();
        }

        if ($this->feeCategories instanceof PersistentCollection) {
            $this->feeCategories->initialize();
        }

        return $this->feeCategories;
    }

    /**
     * @param Collection $feeCategories
     * @return FinanceInvoice
     */
    public function setFeeCategories(Collection $feeCategories): FinanceInvoice
    {
        $this->feeCategories = $feeCategories;
        return $this;
    }

    /**
     * addFeeCategory
     * @param FinanceFeeCategory $feeCategory
     * @return FinanceInvoice
     * 3/06/2020 18:31
     */
    public function addFeeCategory(FinanceFeeCategory $feeCategory) : FinanceInvoice
    {
        if (!$this->getFeeCategories()->contains($feeCategory)) {
            $this->feeCategories->add($feeCategory);
        }

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getInvoiceIssueDate(): ?\DateTimeImmutable
    {
        return $this->invoiceIssueDate;
    }

    /**
     * @param \DateTimeImmutable|null $invoiceIssueDate
     * @return FinanceInvoice
     */
    public function setInvoiceIssueDate(?\DateTimeImmutable $invoiceIssueDate): FinanceInvoice
    {
        $this->invoiceIssueDate = $invoiceIssueDate;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getInvoiceDueDate(): ?\DateTimeImmutable
    {
        return $this->invoiceDueDate;
    }

    /**
     * @param \DateTimeImmutable|null $invoiceDueDate
     * @return FinanceInvoice
     */
    public function setInvoiceDueDate(?\DateTimeImmutable $invoiceDueDate): FinanceInvoice
    {
        $this->invoiceDueDate = $invoiceDueDate;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getPaidDate(): ?\DateTimeImmutable
    {
        return $this->paidDate;
    }

    /**
     * @param \DateTimeImmutable|null $paidDate
     * @return FinanceInvoice
     */
    public function setPaidDate(?\DateTimeImmutable $paidDate): FinanceInvoice
    {
        $this->paidDate = $paidDate;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPaidAmount(): ?float
    {
        return $this->paidAmount;
    }

    /**
     * @param float|null $paidAmount
     * @return FinanceInvoice
     */
    public function setPaidAmount(?float $paidAmount): FinanceInvoice
    {
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     * @return Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment|null $payment
     * @return FinanceInvoice
     */
    public function setPayment(?Payment $payment): FinanceInvoice
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getReminderCount(): ?int
    {
        return $this->reminderCount;
    }

    /**
     * @param int|null $reminderCount
     * @return FinanceInvoice
     */
    public function setReminderCount(?int $reminderCount): FinanceInvoice
    {
        $this->reminderCount = $reminderCount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     * @return FinanceInvoice
     */
    public function setNotes(?string $notes): FinanceInvoice
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string|null $key
     * @return FinanceInvoice
     */
    public function setKey(?string $key): FinanceInvoice
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getCreator(): ?Person
    {
        return $this->creator;
    }

    /**
     * @param Person|null $creator
     * @return FinanceInvoice
     */
    public function setCreator(?Person $creator): FinanceInvoice
    {
        $this->creator = $creator;
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
     * @param \DateTimeImmutable|null $createdOn
     * @return FinanceInvoice
     */
    public function setCreatedOn(?\DateTimeImmutable $createdOn): FinanceInvoice
    {
        $this->createdOn = $createdOn;
        return $this;
    }

    /**
     * @return Person|null
     */
    public function getUpdater(): ?Person
    {
        return $this->updater;
    }

    /**
     * @param Person|null $updater
     * @return FinanceInvoice
     */
    public function setUpdater(?Person $updater): FinanceInvoice
    {
        $this->updater = $updater;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedOn(): ?\DateTimeImmutable
    {
        return $this->updatedOn;
    }

    /**
     * @param \DateTimeImmutable|null $updatedOn
     * @return FinanceInvoice
     */
    public function setUpdatedOn(?\DateTimeImmutable $updatedOn): FinanceInvoice
    {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    /**
     * @return array
     */
    public static function getInvoiceToList(): array
    {
        return self::$invoiceToList;
    }

    /**
     * @return array
     */
    public static function getScheduleTypeList(): array
    {
        return self::$scheduleTypeList;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 4/06/2020 10:01
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 4/06/2020 10:01
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FinanceInvoice` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `academic_year` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `invoicee` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `billing_schedule` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `payment` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `updater` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `invoice_to` varchar(8) NOT NULL DEFAULT 'Family',
                    `schedule_type` varchar(12) NOT NULL DEFAULT 'Ad Hoc',
                    `separated` varchar(1) NOT NULL DEFAULT 'N' COMMENT 'Has this invoice been separated from its schedule in FinanceBillingSchedule? Only applies to scheduled invoices. Separation takes place during invoice issuing.',
                    `status` varchar(16) NOT NULL DEFAULT 'Pending',
                    `invoice_issue_date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
                    `invoice_due_date` date DEFAULT NULL,
                    `paid_date` date NOT NULL COMMENT '(DC2Type:date_immutable)',
                    `paid_amount` decimal(13,2) DEFAULT NULL COMMENT 'The current running total amount paid to this invoice',
                    `reminder_count` smallint(6) NOT NULL DEFAULT '0',
                    `notes` longtext NOT NULL,
                    `key` varchar(40) NOT NULL,
                    `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `updated_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`),
                    KEY `academic_year` (`academic_year`),
                    KEY `billing_schedule` (`billing_schedule`),
                    KEY `payment` (`payment`),
                    KEY `creator` (`creator`),
                    KEY `updater` (`updater`),
                    KEY `invoicee` (`invoicee`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE `__prefix__FinanceInvoiceFeeCategory` (
                    `invoice` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `fee_category` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`invoice`,`fee_category`),
                    KEY `IDX_B392074E90651744` (`invoice`),
                    KEY `IDX_B392074E6472B2C6` (`fee_category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/06/2020 10:00
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FinanceInvoice`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`updater`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`billing_schedule`) REFERENCES `__prefix__FinanceBillingSchedule` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`payment`) REFERENCES `__prefix__Payment` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`invoicee`) REFERENCES `__prefix__FinanceInvoicee` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`);
                ALTER TABLE `__prefix__FinanceInvoicefFeeCategory`
                    ADD CONSTRAINT FOREIGN KEY (`fee_category`) REFERENCES `__prefix__FinanceFeeCategory` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`invoice`) REFERENCES `__prefix__FinanceInvoice` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/06/2020 10:01
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}