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
 * Date: 16/09/2020
 * Time: 16:25
 */
namespace App\Modules\Enrolment\Provider;

use App\Modules\Enrolment\Entity\CourseClassTutor;
use App\Modules\Staff\Entity\Staff;
use App\Provider\AbstractProvider;

/**
 * Class CourseClassStaffProvider
 * @package App\Modules\Enrolment\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassTutorProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected string $entityName = CourseClassTutor::class;
}