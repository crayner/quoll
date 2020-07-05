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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use phpDocumentor\Reflection\Types\Collection;

/**
 * Class FinanceInvoicee
 * @package App\Modules\Finanace\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Finance\Repository\FinanceInvoiceeRepository")
 * @ORM\Table(name="FinanceInvoicee",
 *     indexes={@ORM\Index(name="person",columns={"person"})})
 */
class FinanceInvoicee extends AbstractEntity
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
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     */
    private $person;

    /**
     * @var string
     * @ORM\Column(length=8)
     */
    private $invoiceTo = 'Company';

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $companyName;

    /**
     * @var string|null
     * @ORM\Column(length=100,nullable=true)
     */
    private $companyContact;

    /**
     * @var string|null
     * @ORM\Column(nullable=true,length=191)
     */
    private $companyAddress;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    private $companyEmail;

    /**
     * @var string|null
     * @ORM\Column(length=1,name="company_cc_family",options={"comment": "When company is billed, should family receive a copy?","default": "Y"})
     */
    private $companyCCFamily = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $companyPhone;

    /**
     * @var string|null
     * @ORM\Column(length=1,options={"comment": "Should company pay all invoices?.","default": "Y"})
     */
    private $companyAll = 'Y';

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="App\Modules\Finance\Entity\FinanceFeeCategory")
     * @ORM\JoinTable(name="FinanceInvoiceeFeeCategory",
     *      joinColumns={@ORM\JoinColumn(name="invoicee",referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="fee_category",referencedColumnName="id")}
     *  )
     * If companyAll is N, list category IDs for company to pay here.
     */
    private $feeCategories;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return FinanceInvoicee
     */
    public function setId(?string $id): FinanceInvoicee
    {
        $this->id = $id;
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
     * @return FinanceInvoicee
     */
    public function setPerson(?Person $person): FinanceInvoicee
    {
        $this->person = $person;
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
     * @return FinanceInvoicee
     */
    public function setInvoiceTo(string $invoiceTo): FinanceInvoicee
    {
        $this->invoiceTo = in_array($invoiceTo, self::getInvoiceToList()) ? $invoiceTo : 'Company';
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @param string|null $companyName
     * @return FinanceInvoicee
     */
    public function setCompanyName(?string $companyName): FinanceInvoicee
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyContact(): ?string
    {
        return $this->companyContact;
    }

    /**
     * @param string|null $companyContact
     * @return FinanceInvoicee
     */
    public function setCompanyContact(?string $companyContact): FinanceInvoicee
    {
        $this->companyContact = $companyContact;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    /**
     * @param string|null $companyAddress
     * @return FinanceInvoicee
     */
    public function setCompanyAddress(?string $companyAddress): FinanceInvoicee
    {
        $this->companyAddress = $companyAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    /**
     * @param string|null $companyEmail
     * @return FinanceInvoicee
     */
    public function setCompanyEmail(?string $companyEmail): FinanceInvoicee
    {
        $this->companyEmail = $companyEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyCCFamily(): ?string
    {
        return $this->companyCCFamily;
    }

    /**
     * @param string|null $companyCCFamily
     * @return FinanceInvoicee
     */
    public function setCompanyCCFamily(?string $companyCCFamily): FinanceInvoicee
    {
        $this->companyCCFamily = self::checkBoolean($companyCCFamily, null);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    /**
     * @param string|null $companyPhone
     * @return FinanceInvoicee
     */
    public function setCompanyPhone(?string $companyPhone): FinanceInvoicee
    {
        $this->companyPhone = $companyPhone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompanyAll(): ?string
    {
        return $this->companyAll;
    }

    /**
     * @param string|null $companyAll
     * @return FinanceInvoicee
     */
    public function setCompanyAll(?string $companyAll): FinanceInvoicee
    {
        $this->companyAll = self::checkBoolean($companyAll, null);
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
     * @return FinanceInvoicee
     */
    public function setFeeCategories(Collection $feeCategories): FinanceInvoicee
    {
        $this->feeCategories = $feeCategories;
        return $this;
    }

    /**
     * addFeeCategory
     * @param FinanceFeeCategory $feeCategory
     * @return FinanceInvoicee
     * 3/06/2020 18:31
     */
    public function addFeeCategory(FinanceFeeCategory $feeCategory) : FinanceInvoicee
    {
        if (!$this->getFeeCategories()->contains($feeCategory)) {
            $this->feeCategories->add($feeCategory);
        }

        return $this;
    }

    /**
     * @return array
     */
    public static function getInvoiceToList(): array
    {
        return FinanceInvoice::getInvoiceToList();
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     * 4/06/2020 10:09
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }

    /**
     * create
     * @return array|string[]
     * 4/06/2020 10:09
     */
    public function create(): array
    {
        return ["CREATE TABLE `__prefix__FinanceInvoicee` (
                    `id` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `person` char(36) DEFAULT NULL COMMENT '(DC2Type:guid)',
                    `invoice_to` varchar(8) NOT NULL,
                    `company_name` varchar(100) DEFAULT NULL,
                    `company_contact` varchar(100) DEFAULT NULL,
                    `company_address` varchar(191) DEFAULT NULL,
                    `company_email` longtext,
                    `company_cc_family` varchar(1) NOT NULL DEFAULT 'Y' COMMENT 'When company is billed, should family receive a copy?',
                    `company_phone` varchar(20) DEFAULT NULL,
                    `company_all` varchar(1) NOT NULL DEFAULT 'Y' COMMENT 'Should company pay all invoices?.',
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
                "CREATE TABLE `__prefix__FinanceInvoiceeFeeCategory` (
                    `invoicee` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `fee_category` char(36) NOT NULL COMMENT '(DC2Type:guid)',
                    PRIMARY KEY (`invoicee`,`fee_category`),
                    KEY `IDX_E57756699EFB9AC4` (`invoicee`),
                    KEY `IDX_E57756696472B2C6` (`fee_category`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"];
    }

    /**
     * foreignConstraints
     * @return string
     * 4/06/2020 10:10
     */
    public function foreignConstraints(): string
    {
        return "ALTER TABLE `__prefix__FinanceInvoicee`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);
                ALTER TABLE `__prefix__FinanceInvoiceeFeeCategory`
                    ADD CONSTRAINT FOREIGN KEY (`fee_category`) REFERENCES `__prefix__FinanceFeeCategory` (`id`),
                    ADD CONSTRAINT FOREIGN KEY (`invoicee`) REFERENCES `__prefix__FinanceInvoicee` (`id`);";
    }

    /**
     * getVersion
     * @return string
     * 4/06/2020 10:17
     */
    public static function getVersion(): string
    {
        return static::VERSION;
    }
}