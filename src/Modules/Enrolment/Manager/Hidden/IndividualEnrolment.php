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
use App\Modules\Enrolment\Entity\CourseClassStudent;
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
     * @var array
     */
    private array $classes = [];

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     * @return IndividualEnrolment
     */
    public function setClasses(array $classes): IndividualEnrolment
    {
        $this->classes = $classes;
        return $this;
    }


}
