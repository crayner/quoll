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
 * Date: 15/05/2020
 * Time: 11:52
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class Phone
 * @package App\Modules\People\Validator
 * @Annotation
 */
class Phone extends Constraint
{
    const INVALID_PHONE_ERROR = 'ebe6b7d8-df78-44ae-ad9d-3c1aacc2a6b5';

    protected static $errorNames = [
        self::INVALID_PHONE_ERROR => 'INVALID_PHONE_ERROR',
    ];

    public $message = 'The phone number {value} is not valid for {country}';

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