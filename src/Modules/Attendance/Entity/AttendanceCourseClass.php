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
 * Date: 19/10/2020
 * Time: 12:45
 */
namespace App\Modules\Attendance\Entity;

use App\Manager\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AttendanceRecorderLogClass
 *
 * 19/10/2020 12:51
 * @package App\Modules\Attendance\Entity
 * @author Craig Rayner <craig@craigrayner.com>
 * @ORM\Entity(repositoryClass="App\Modules\Attendance\Repository\AttendanceCourseClassRepository")
 * @ORM\Table(name="AttendanceCourseClass"
 * )
 */
class AttendanceCourseClass extends AbstractEntity
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
     * Id
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return isset($this->id) ?$this->id : null;
    }

    /**
     * Id
     *
     * @param string|null $id
     * @return AttendanceCourseClass
     */
    public function setId(?string $id): AttendanceCourseClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * toArray
     *
     * 19/10/2020 12:52
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = null): array
    {
        return [];
    }
}
