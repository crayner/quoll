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
 * Date: 2/07/2020
 * Time: 08:46
 */
namespace App\Modules\People\Entity;

use App\Manager\AbstractEntity;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Contact
 * @package App\Modules\People\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\ContactRepository")
 * @ORM\Table(name="Contact",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person",columns={"person"})
 *  },
 *  indexes={
 *      @ORM\Index(name="personal_phone",columns={"personal_phone"}),
 *      @ORM\Index(name="physical_address",columns={"physical_address"}),
 *      @ORM\Index(name="postal_address",columns={"postal_address"})
 *  }
 * )
 */
class Contact extends AbstractEntity
{
    const VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person")
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Person $person;

    /**
     * @var string|null
     * @ORM\Column(length=75,nullable=true)
     */
    private $email;

    /**
     * @var string|null
     * @ORM\Column(length=75,nullable=true)
     */
    private $emailAlternate;

    /**
     * @var Address|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="physical_address",referencedColumnName="id",nullable=true)
     */
    private $physicalAddress;

    /**
     * @var Address|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Address")
     * @ORM\JoinColumn(name="postal_address",referencedColumnName="id",nullable=true)
     */
    private $postalAddress;

    /**
     * @var Phone|null
     * @ORM\ManyToOne(targetEntity="App\Modules\People\Entity\Phone")
     * @ORM\JoinColumn(name="personal_phone",referencedColumnName="id",nullable=true)
     */
    private $personalPhone;

    /**
     * @var Collection|null
     * @ORM\ManyToMany(targetEntity="App\Modules\People\Entity\Phone")
     * @ORM\JoinTable(name="ContactAdditionalPhone",
     *  joinColumns={
     *     @ORM\JoinColumn(name="contact",referencedColumnName="id")},
     *  inverseJoinColumns={
     *     @ORM\JoinColumn(name="phone",referencedColumnName="id")}
     * )
     */
    private ?Collection $additionalPhones;

    /**
     * @var string|null
     * @ORM\Column(length=191,nullable=true)
     */
    private $website;

    /**
     * @var string|null
     * @ORM\Column(length=90,nullable=true)
     */
    private $profession;

    /**
     * @var string|null
     * @ORM\Column(length=90,nullable=true)
     */
    private ?string $employer = null;

    /**
     * @var string|null
     * @ORM\Column(length=90,nullable=true)
     */
    private ?string $jobTitle = null;

    /**
     * Contact constructor.
     * @param Person|null $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person)
            ->setAdditionalPhones(new ArrayCollection())
        ;
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
     * @return Contact
     */
    public function setId(?string $id): Contact
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
     * setPerson
     *
     * 6/09/2020 07:58
     * @param Person|null $person
     * @return $this
     */
    public function setPerson(?Person $person): Contact
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * setEmail
     * @param string|null $email
     * @return $this|AbstractEntity
     * 2/07/2020 09:38
     */
    public function setEmail(?string $email): AbstractEntity
    {
        $this->email = mb_substr($email, 0, 75);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmailAlternate(): ?string
    {
        return $this->emailAlternate;
    }

    /**
     * setEmailAlternate
     * @param string|null $emailAlternate
     * @return $this|AbstractEntity
     * 2/07/2020 09:39
     */
    public function setEmailAlternate(?string $emailAlternate): AbstractEntity
    {
        $this->emailAlternate = mb_substr($emailAlternate, 0, 75);
        return $this;
    }

    /**
     * @return Address|null
     */
    public function getPhysicalAddress(): ?Address
    {
        return $this->physicalAddress;
    }

    /**
     * Address.
     *
     * @param Address|null $address
     * @return AbstractEntity
     */
    public function setPhysicalAddress(?Address $address): AbstractEntity
    {
        $this->physicalAddress = $address;
        return $this;
    }

    /**
     * @return Address|null
     */
    public function getPostalAddress(): ?Address
    {
        return $this->postalAddress;
    }

    /**
     * PostalAddress.
     *
     * @param Address|null $postalAddress
     * @return AbstractEntity
     */
    public function setPostalAddress(?Address $postalAddress): AbstractEntity
    {
        $this->postalAddress = $postalAddress;
        return $this;
    }

    /**
     * @return Phone|null
     */
    public function getPersonalPhone(): ?Phone
    {
        return $this->personalPhone;
    }

    /**
     * PersonalPhone.
     *
     * @param Phone|null $personalPhone
     * @return AbstractEntity
     */
    public function setPersonalPhone(?Phone $personalPhone): AbstractEntity
    {
        $this->personalPhone = $personalPhone;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAdditionalPhones(): Collection
    {
        if (null === $this->additionalPhones)
            $this->additionalPhones = new ArrayCollection();

        if ($this->additionalPhones instanceof PersistentCollection)
            $this->additionalPhones->initialize();

        return $this->additionalPhones;
    }

    /**
     * AdditionalPhones.
     *
     * @param Collection|null $additionalPhones
     * @return AbstractEntity
     */
    public function setAdditionalPhones(?Collection $additionalPhones): AbstractEntity
    {
        $this->additionalPhones = $additionalPhones;
        return $this;
    }

    /**
     * addAdditionalPhone
     * @param Phone $phone
     * @return $this
     */
    public function addAdditionalPhone(Phone $phone): AbstractEntity
    {
        if ($this->getAdditionalPhones()->contains($phone))
            return $this;

        $this->additionalPhones->add($phone);

        return $this;
    }

    /**
     * removeAdditionalPhone
     * @param Phone $phone
     * @return $this
     */
    public function removeAdditionalPhone(Phone $phone): AbstractEntity
    {
        $this->getAdditionalPhones()->removeElement($phone);
        return $this;
    }

    /**
     * getPhoneList
     * @param bool $includeFamily
     * @return array
     * 2/07/2020 11:21
     */
    public function getPhoneList(bool $includeFamily = false): array
    {
        $result = [];
        if ($this->getPersonalPhone()) {
            $result[] = $this->getPersonalPhone()->__toString();
        }
        foreach ($this->getAdditionalPhones() as $phone) {
            $result[] = $phone->__toString();
        }
        if ($includeFamily) {
            foreach (ProviderFactory::create(Phone::class)->getFamilyPhonesOfContact($this) as $phone) {
                $result[] = $phone->__toString();
            }
        }
        return $result;
    }

    /**
     * @return null|string
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param null|string $website
     * @return Contact
     */
    public function setWebsite(?string $website): Contact
    {
        $this->website = mb_substr($website, 0, 191);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getProfession(): ?string
    {
        return $this->profession;
    }

    /**
     * @param null|string $profession
     * @return Contact
     */
    public function setProfession(?string $profession): Contact
    {
        $this->profession = mb_substr($profession, 0, 90);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmployer(): ?string
    {
        return $this->employer;
    }

    /**
     * @param null|string $employer
     * @return Contact
     */
    public function setEmployer(?string $employer): Contact
    {
        $this->employer = mb_substr($employer, 0, 90);
        return $this;
    }

    /**
     * @return null|string
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @param null|string $jobTitle
     * @return Contact
     */
    public function setJobTitle(?string $jobTitle): Contact
    {
        $this->jobTitle = mb_substr($jobTitle, 0, 90);
        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * getFullNameReversed
     *
     * 25/08/2020 13:53
     * @return string
     */
    public function getFullNameReversed(): string
    {
        return $this->getPerson()->formatName('Reversed');
    }

    /**
     * getFullName
     *
     * 25/08/2020 13:53
     * @param string $style
     * @return string
     */
    public function getFullName(string $style = 'Standard'): string
    {
        return $this->getPerson()->formatName($style);
    }
}
