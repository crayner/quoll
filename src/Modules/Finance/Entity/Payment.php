<?php
/**
 * Created by PhpStorm.
 *
* Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace App\Modules\Finance\Entity;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Person;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Payment
 * @package App\Modules\Finance\Entity
 * @ORM\Entity(repositoryClass="App\Modules\Finance\Repository\PaymentRepository")
 * @ORM\Table(name="Payment",
 *     indexes={@ORM\Index(name="person",columns={"person"})})
 */
class Payment implements EntityInterface
{
    CONST VERSION = '20200401';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=50, name="foreign_table")
     */
    private $foreignTable;

    /**
     * @var string|null
     * @ORM\Column(length=36,name="foreign_table_id")
     */
    private $foreignTableID;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person", referencedColumnName="id")
     * Person recording the transaction
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=16, options={"default": "Online"})
     */
    private $type = 'Online';

    /**
     * @var array
     */
    private static $typeList = ['Online','Bank Transfer','Cash','Cheque','Other','Credit Card'];

    /**
     * @var string|null
     * @ORM\Column(length=8, options={"comment": "Complete means paid in one go, partial is part of a set of payments, and final is last in a set of payments.", "default": "Complete"})
     */
    private $status = 'Complete';

    /**
     * @var array
     */
    private static $statusList = ['Complete','Partial','Final','Failure'];

    /**
     * @var float|null
     * @ORM\Column(type="decimal", precision=13, scale=2)
     */
    private $amount;

    /**
     * @var string|null
     * @ORM\Column(length=6, nullable=true)
     */
    private $gateway = 'Paypal';

    /**
     * @var string|null
     * @ORM\Column(length=12, nullable=true)
     * @Assert\Choice(callback="getOnlineTransactionStatusList")
     */
    private $onlineTransactionStatus;

    /**
     * @var array
     */
    private static $onlineTransactionStatusList = ['Success', 'Failure'];

    /**
     * @var string|null
     * @ORM\Column(length=50, nullable=true)
     */
    private $paymentToken;

    /**
     * @var string|null
     * @ORM\Column(length=50, nullable=true)
     */
    private $paymentPayerID;

    /**
     * @var string|null
     * @ORM\Column(length=50, nullable=true)
     */
    private $paymentTransactionID;

    /**
     * @var string|null
     * @ORM\Column(length=50, nullable=true)
     */
    private $paymentReceiptID;

    /**
     * @var \DateTimeImmutable|null
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $timestamp;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Id.
     *
     * @param string|null $id
     * @return Payment
     */
    public function setId(?string $id): Payment
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @param string|null $foreignTable
     * @return Payment
     */
    public function setForeignTable(?string $foreignTable): Payment
    {
        $this->foreignTable = $foreignTable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getForeignTableID(): ?string
    {
        return $this->foreignTableID;
    }

    /**
     * ForeignTableID.
     *
     * @param string|null $foreignTableID
     * @return Payment
     */
    public function setForeignTableID(?string $foreignTableID): Payment
    {
        $this->foreignTableID = $foreignTableID;
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
     * @return Payment
     */
    public function setPerson(?Person $person): Payment
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return Payment
     */
    public function setType(?string $type): Payment
    {
        $this->type = in_array($type, self::getTypeList()) ? $type : 'Online' ;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     * @return Payment
     */
    public function setStatus(?string $status): Payment
    {
        $this->status = in_array($status, self::getStatusList()) ? $status : 'Complete';
        return $this;
    }

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     * @return Payment
     */
    public function setAmount(?float $amount): Payment
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    /**
     * @param string|null $gateway
     * @return Payment
     */
    public function setGateway(?string $gateway): Payment
    {
        $this->gateway = $gateway ? 'Paypal' : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOnlineTransactionStatus(): ?string
    {
        return $this->onlineTransactionStatus;
    }

    /**
     * @param string|null $onlineTransactionStatus
     * @return Payment
     */
    public function setOnlineTransactionStatus(?string $onlineTransactionStatus): Payment
    {
        $this->onlineTransactionStatus = in_array($onlineTransactionStatus, self::getOnlineTransactionStatusList()) ? $onlineTransactionStatus : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    /**
     * @param string|null $paymentToken
     * @return Payment
     */
    public function setPaymentToken(?string $paymentToken): Payment
    {
        $this->paymentToken = $paymentToken;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentPayerID(): ?string
    {
        return $this->paymentPayerID;
    }

    /**
     * @param string|null $paymentPayerID
     * @return Payment
     */
    public function setPaymentPayerID(?string $paymentPayerID): Payment
    {
        $this->paymentPayerID = $paymentPayerID;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentTransactionID(): ?string
    {
        return $this->paymentTransactionID;
    }

    /**
     * @param string|null $paymentTransactionID
     * @return Payment
     */
    public function setPaymentTransactionID(?string $paymentTransactionID): Payment
    {
        $this->paymentTransactionID = $paymentTransactionID;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentReceiptID(): ?string
    {
        return $this->paymentReceiptID;
    }

    /**
     * @param string|null $paymentReceiptID
     * @return Payment
     */
    public function setPaymentReceiptID(?string $paymentReceiptID): Payment
    {
        $this->paymentReceiptID = $paymentReceiptID;
        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimestamp(): ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTimeImmutable|null $timestamp
     */
    public function setTimestamp(?\DateTimeImmutable $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
    /**
     * @return array
     */
    public static function getTypeList(): array
    {
        return self::$typeList;
    }

    /**
     * @return array
     */
    public static function getStatusList(): array
    {
        return self::$statusList;
    }

    /**
     * @return array
     */
    public static function getOnlineTransactionStatusList(): array
    {
        return self::$onlineTransactionStatusList;
    }

    public function toArray(?string $name = null): array
    {
       return [];
    }

    public function create(): string
    {
        return "CREATE TABLE `__prefix__Payment` (
                    `id` CHAR(36) NOT NULL COMMENT '(DC2Type:guid)',
                    `foreign_table` CHAR(50) NOT NULL,
                    `foreign_table_id` CHAR(36) DEFAULT NULL,
                    `type` CHAR(16) NOT NULL DEFAULT 'Online',
                    `status` CHAR(8) NOT NULL DEFAULT 'Complete' COMMENT 'Complete means paid in one go, partial is part of a set of payments, and final is last in a set of payments.',
                    `amount` decimal(13,2) NOT NULL,
                    `gateway` CHAR(6) DEFAULT NULL,
                    `online_transaction_status` CHAR(12) DEFAULT NULL,
                    `payment_token` CHAR(50) DEFAULT NULL,
                    `payment_payer_id` CHAR(50) DEFAULT NULL,
                    `payment_transaction_id` CHAR(50) DEFAULT NULL,
                    `payment_receipt_id` CHAR(50) DEFAULT NULL,
                    `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)',
                    `person` CHAR(36) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `person` (`person`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=ut8mb4_unicode_ci;";
    }

    public function foreignConstraints(): string
    {
        return 'ALTER TABLE `__prefix__Payment`
                    ADD CONSTRAINT FOREIGN KEY (`person`) REFERENCES `__prefix__Person` (`id`);';
    }

    public function coreData(): string
    {
        return '';
    }

    public static function getVersion(): string
    {
        return self::VERSION;
    }
}
