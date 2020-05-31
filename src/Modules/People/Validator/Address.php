<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 7/05/2020
 * Time: 10:10
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Address
 * @package App\Modules\People\Validator
 * @Annotation()
 */
class Address extends Constraint
{
    const DUPLICATE_ADDRESS_ERROR = 'bab3baf0-a65b-498f-a224-0021dc4f37e9';

    protected static $errorNames = [
        self::DUPLICATE_ADDRESS_ERROR => 'DUPLICATE_ADDRESS_ERROR',
    ];

    public $message = 'The address is not unique';

    public $transDomain = 'People';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}