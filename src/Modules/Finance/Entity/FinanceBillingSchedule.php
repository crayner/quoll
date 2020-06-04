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
namespace App\Modules\Finance\Entity;

use App\Manager\AbstractEntity;
use App\Manager\Traits\BooleanList;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FinanceBillingSchedule
 * @package App\Modules\Finance\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Finance\Repository\FinanceBillingScheduleRepository")
 * @ORM\Table(name="FinanceBillingSchedule",
 *     indexes={@ORM\Index(name="academic_year",columns={"academic_year"}),
 *     @ORM\Index(name="creator",columns={"creator"}),
 *     @ORM\Index(name="updater",columns={"updater"})})
 */
class FinanceBillingSchedule extends AbstractEntity
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
    private $AcademicYear;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",name="invoice_issue_date",nullable=true)
     */
    private $invoiceIssueDate;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="date_immutable",name="invoice_due_date",nullable=true)
     */
    private $invoiceDueDate;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="creator",referencedColumnName="id")
     */
    private $creator;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", name="created_on")
     */
    private $createdOn;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="updater", referencedColumnName="id")
     */
    private $updater;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", name="updated_on")
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
     * @return FinanceBillingSchedule
     */
    public function setId(?string $id): FinanceBillingSchedule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AcademicYear|null
     */
    public function getAcademicYear(): ?AcademicYear
    {
        return $this->AcademicYear;
    }

    /**
     * @param AcademicYear|null $AcademicYear
     * @return FinanceBillingSchedule
     */
    public function setAcademicYear(?AcademicYear $AcademicYear): FinanceBillingSchedule
    {
        $this->AcademicYear = $AcademicYear;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return FinanceBillingSchedule
     */
    public function setName(?string $name): FinanceBillingSchedule
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return FinanceBillingSchedule
     */
    public function setDescription(?string $description): FinanceBillingSchedule
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return $this->active;
    }

    /**
     * setActive
     * @param string|null $active
     * @return FinanceBillingSchedule
     */
    public function setActive(?string $active): FinanceBillingSchedule
    {
        $this->active = self::checkBoolean($active, 'Y');
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
     * @return FinanceBillingSchedule
     */
    public function setInvoiceIssueDate(?\DateTimeImmutable $invoiceIssueDate): FinanceBillingSchedule
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
     * @return FinanceBillingSchedule
     */
    public function setInvoiceDueDate(?\DateTimeImmutable $invoiceDueDate): FinanceBillingSchedule
    {
        $this->invoiceDueDate = $invoiceDueDate;
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
     * @return FinanceBillingSchedule
     */
    public function setCreator(?Person $creator): FinanceBillingSchedule
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
     * @return FinanceBillingSchedule
     */
    public function setCreatedOn(?\DateTimeImmutable $createdOn): FinanceBillingSchedule
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
     * @return FinanceBillingSchedule
     */
    public function setUpdater(?Person $updater): FinanceBillingSchedule
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
     * @return FinanceBillingSchedule
     */
    public function setUpdatedOn(?\DateTimeImmutable $updatedOn): FinanceBillingSchedule
    {
        $this->updatedOn = $updatedOn;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 4/06/2020 09:54
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FinanceBillingSchedule` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `academic_year` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `creator` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `updater` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `name` varchar(100) NOT NULL,
                    `description` longtext NOT NULL,
                    `active` varchar(1) NOT NULL DEFAULT 'Y',
                    `invoice_issue_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `invoice_due_date` date DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
                    `created_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    `updated_on` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                    PRIMARY KEY (`id`),
                    KEY `academic_year` (`academic_year`),
                    KEY `creator` (`creator`),
                    KEY `updater` (`updater`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FinancebillingSchedule`
                    ADD CONSTRAINT FOREIGN KEY (`academic_year`) REFERENCES `__prefix__AcademicYear` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`updater`) REFERENCES `__prefix__Person` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`creator`) REFERENCES `__prefix__Person` (`id`);";
    }

    public static function getVersion(): string
    {
        // TODO: Implement getVersion() method.
    }
}