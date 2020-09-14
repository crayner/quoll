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
 * Date: 11/09/2020
 * Time: 07:55
 */
namespace App\Modules\Enrolment\Manager\Hidden;

use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\CourseClassPerson;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class IndividualEnrolment
 * @package App\Modules\Enrolment\Manager\Hidden
 * @author Craig Rayner <craig@craigrayner.com>
 */
class IndividualEnrolment
{
    /**
     * @var Collection
     */
    private Collection $classes;

    /**
     * @var string
     */
    private string $role;

    /**
     * getClasses
     *
     * 11/09/2020 07:58
     * @return Collection
     */
    public function getClasses(): Collection
    {
        if (!isset($this->classes)) $this->classes = new ArrayCollection();
        return $this->classes;
    }

    /**
     * @param Collection $classes
     * @return IndividualEnrolment
     */
    public function setClasses(Collection $classes): IndividualEnrolment
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * addClass
     *
     * 11/09/2020 08:00
     * @param CourseClass $class
     * @return $this
     */
    public function addClass(CourseClass $class): IndividualEnrolment
    {
        if ($this->getClasses()->contains($class)) return $this;

        $this->classes->add($class);

        return $this;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return IndividualEnrolment
     */
    public function setRole(string $role): IndividualEnrolment
    {
        $this->role = $role;
        return $this;
    }

    /**
     * getRoleList
     *
     * 11/09/2020 07:57
     * @return array
     */
    public static function getRoleList(): array
    {
        return CourseClassPerson::getRoleListCurrent();
    }
}
