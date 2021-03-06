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
    private ?string $id;

    /**
     * @var Person|null
     * @ORM\OneToOne(targetEntity="App\Modules\People\Entity\Person",cascade={"persist"})
     * @ORM\JoinColumn(name="person",referencedColumnName="id",nullable=false)
     * @Assert\NotBlank()
     */
    private ?Person $person = null;

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
     * @var Collection|CustomFieldData[]
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\CustomFieldData",mappedBy="careGiver",cascade={"all"},orphanRemoval=true)
     */
    private $customData;

    /**
     * CareGiver constructor.
     * @param Person|null $person
     */
    public function __construct(?Person $person = null)
    {
        if ($person !== null) $person->setCareGiver($this);
        $this->setVehicleRegistration(true)
            ->setPerson($person)
            ->setCustomData(new ArrayCollection())
            ->setMemberOfFamilies(new ArrayCollection())
            ->setReceiveNotificationEmails(true);
    }

    /**
     * getId
     *
     * 29/08/2020 12:07
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ? $this->id : null;
    }

    /**
     * setId
     *
     * 29/08/2020 12:07
     * @param string $id
     * @return $this
     */
    public function setId(string $id): CareGiver
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
     * 29/08/2020 12:13
     * @param Person|null $person
     * @return $this
     */
    public function setPerson(?Person $person): CareGiver
    {
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

    /**
     * @return CustomFieldData[]|Collection
     */
    public function getCustomData(): Collection
    {
        if ($this->customData === null) $this->customData = new ArrayCollection();

        if ($this->customData instanceof PersistentCollection) $this->customData->initialize();

        $iterator = $this->customData->getIterator();
        $iterator->uasort(
            function (CustomFieldData $a, CustomFieldData $b) {
                return $a->getCustomField()->getDisplayOrder() <= $b->getCustomField()->getDisplayOrder() ? -1 : 1;
            }
        );

        $this->customData = new ArrayCollection();
        foreach(iterator_to_array($iterator, false) as $item) {
            $this->addCustomData($item);
        }

        return $this->customData;
    }

    /**
     * setCustomData
     * @param Collection|null $customData
     * @return $this
     * 29/07/2020 11:26
     */
    public function setCustomData(?Collection $customData): CareGiver
    {
        $this->customData = $customData;
        return $this;
    }

    /**
     * addCustomData
     * @param CustomFieldData $data
     * @return $this
     * 29/07/2020 11:26
     */
    public function addCustomData(CustomFieldData $data): CareGiver
    {
        if ($data === null || $this->getCustomData()->containsKey($data->getCustomField()->getId())) return $this;

        $this->customData->set($data->getCustomField()->getId(), $data);

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

    /**
     * getFullNameReversed
     *
     * 24/08/2020 09:52
     * @return string
     */
    public function getFullNameReversed(): string
    {
        return $this->getPerson()->formatName('Reversed', 'CareGiver');
    }

    /**
     * getFullName
     *
     * 24/08/2020 09:52
     * @param string $style
     * @return string
     */
    public function getFullName(string $style = 'Standard'): string
    {
        return $this->getPerson()->formatName($style, 'CareGiver');
    }

}
