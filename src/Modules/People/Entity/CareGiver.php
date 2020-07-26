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
use App\Modules\System\Entity\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CareGiver
 * @package App\Modules\People\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\CareGiverRepository")
 * @ORM\Table(name="CareGiver",
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="person",columns={"person"})
 *  }
 * )
 * @UniqueEntity("person")
 */
class CareGiver extends AbstractEntity
{
    CONST VERSION = '1.0.00';

    /**
     * @var string|null
     * @ORM\Id()
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var Person
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",inversedBy="careGiver",cascade={"persist"})
     * @ORM\JoinColumn(name="person",referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var string|null
     * @ORM\Column(length=20,nullable=true)
     */
    private $vehicleRegistration;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $receiveNotificationEmails = true;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private $viewCalendarSchool = true;

    /**
     * @var Collection|FamilyMemberCareGiver[]|null
     * @ORM\OneToMany(targetEntity="FamilyMemberCareGiver",mappedBy="careGiver")
     */
    private $memberOfFamilies;

    /**
     * CareGiver constructor.
     * @param Person|null $person
     */
    public function __construct(?Person $person = null)
    {
        $this->setPerson($person);
        $this->setVehicleRegistration(true);
        $this->setReceiveNotificationEmails(true);
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
     * @return CareGiver
     */
    public function setId(?string $id): CareGiver
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson(): Person
    {
        return $this->person;
    }

    /**
     * setPerson
     * @param Person|null $person
     * @param bool $reflect
     * @return $this
     * 2/07/2020 09:11
     */
    public function setPerson(?Person $person, bool $reflect = true): CareGiver
    {
        if ($person && $person instanceof Person) {
            $person->setCareGiver($this, false);
        }
        $this->person = $person;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVehicleRegistration(): ?string
    {
        return $this->vehicleRegistration;
    }

    /**
     * @param string|null $vehicleRegistration
     * @return CareGiver
     */
    public function setVehicleRegistration(?string $vehicleRegistration): CareGiver
    {
        $this->vehicleRegistration = $vehicleRegistration;
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
     * @param bool|null $receiveNotificationEmails
     * @return CareGiver
     */
    public function setReceiveNotificationEmails(?bool $receiveNotificationEmails): CareGiver
    {
        $this->receiveNotificationEmails = (bool)$receiveNotificationEmails;
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
     * @return CareGiver
     */
    public function setViewCalendarSchool(?bool $viewCalendarSchool): CareGiver
    {
        $this->viewCalendarSchool = (bool)$viewCalendarSchool;
        return $this;
    }

    /**
     * getMemberOfFamilies
     * @return Collection
     * 18/07/2020 10:42
     */
    public function getMemberOfFamilies(): Collection
    {
        if ($this->memberOfFamilies === null) {
            $this->memberOfFamilies = new ArrayCollection();
        }

        if ($this->memberOfFamilies instanceof PersistentCollection) {
            $this->memberOfFamilies->initialize();
        }

        return $this->memberOfFamilies;
    }

    /**
     * setMemberOfFamilies
     * @param Collection|null $memberOfFamilies
     * @return $this
     * 18/07/2020 10:43
     */
    public function setMemberOfFamilies(?Collection $memberOfFamilies)
    {
        $this->memberOfFamilies = $memberOfFamilies;
        return $this;
    }

    /**
     * addMemberOfFamily
     * @param FamilyMemberCareGiver|null $parent
     * @return $this
     * 18/07/2020 10:40
     */
    public function addMemberOfFamily(?FamilyMemberCareGiver $parent): CareGiver
    {
        if (null === $parent || $this->getMemberOfFamilies()->contains($parent)) {
            return $this;
        }

        $this->memberOfFamilies->add($parent);

        return $this;
    }

    public function toArray(?string $name = null): array
    {
        // TODO: Implement toArray() method.
    }

    /**
     * isEqualTo
     * @param CareGiver $careGiver
     * @return bool
     * 26/07/2020 09:54
     */
    public function isEqualTo(CareGiver $careGiver): bool
    {
        return $careGiver->getId() === $this->getId() && $careGiver->getPerson()->isEqualTo($this->getPerson());
    }
}