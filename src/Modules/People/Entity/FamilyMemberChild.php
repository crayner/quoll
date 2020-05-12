<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 11/05/2020
 * Time: 16:24
 */
namespace App\Modules\People\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class FamilyMemberChild
 * @package App\Modules\People\Entity
 * @ORM\Entity(repositoryClass="App\Modules\People\Repository\FamilyMemberChildRepository")
 * @ORM\Table(indexes={@ORM\Index(name="person", columns={"person"}),
 *     @ORM\Index(name="family", columns={"family"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="family_member", columns={"family","person"})})
 * @UniqueEntity(fields={"family","person"},errorPath="person")
 */
class FamilyMemberChild extends FamilyMember
{
    /**
     * @var Collection|FamilyRelationship[]
     * @ORM\OneToMany(targetEntity="App\Modules\People\Entity\FamilyRelationship",mappedBy="child",orphanRemoval=true)
     */
    private $relationships;

    /**
     * @return Collection|FamilyRelationship[]
     */
    public function getRelationships(): Collection
    {
        if (null === $this->relationships)
            $this->relationships = new ArrayCollection();

        if ($this->relationships instanceof PersistentCollection)
            $this->relationships->initialize();

        return $this->relationships;
    }

    /**
     * Relationships.
     *
     * @param Collection|FamilyRelationship[] $relationships
     * @return FamilyMemberChild
     */
    public function setRelationships(Collection $relationships): FamilyMemberChild
    {
        if ($relationships instanceof PersistentCollection)
            $relationships->initialize();

        $this->relationships = $relationships;
        return $this;
    }
}