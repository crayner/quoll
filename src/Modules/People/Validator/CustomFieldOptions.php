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
 * Date: 18/05/2020
 * Time: 15:36
 */
namespace App\Modules\People\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class CustomFieldOptions
 * @package App\Modules\People\Validator
 * @Annotations()
 */
class CustomFieldOptions extends Constraint
{
    const INVALID_OPTIONS_ERROR = 'ddcbb942-acca-4ebc-970b-8e615a349d9b';

    protected static $errorNames = [
        self::INVALID_OPTIONS_ERROR => 'INVALID_OPTIONS_ERROR',
    ];

    public $message = 'The option {value} is not valid for {type}.';

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