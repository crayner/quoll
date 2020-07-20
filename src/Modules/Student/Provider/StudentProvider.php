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
 * Date: 20/07/2020
 * Time: 10:24
 */
namespace App\Modules\Student\Provider;

use App\Modules\Student\Entity\Student;
use App\Provider\AbstractProvider;

/**
 * Class StudentProvider
 * @package App\Modules\Student\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class StudentProvider extends AbstractProvider
{
    /**
     * @var string
     */
    protected $entityName = Student::class;
}
