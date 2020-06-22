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
 * Date: 13/05/2020
 * Time: 14:04
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class PostCode
 * @package App\Modules\People\Validator
 * @Annotation()
 */
class PostCode extends Constraint
{
    const INVALID_POSTCODE_ERROR = '9e2814d1-0151-4545-8c15-adec8d3403dd';

    protected static $errorNames = [
        self::INVALID_POSTCODE_ERROR => 'INVALID_POSTCODE_ERROR',
    ];

    public $message = 'The postcode {value} is not valid.';

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