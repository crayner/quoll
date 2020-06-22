<?php
/**
 * Created by PhpStorm.
 *
  * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/07/2019
 * Time: 14:25
 */
namespace App\Modules\System\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class MySQLConnection
 * @package App\Modules\System\Validator
 */
class MySQLConnection extends Constraint
{
    const MYSQL_CONNECTION_ERROR = 'ce3837de-a1a1-46f1-895d-5d4ddfb278c1';
    const MYSQL_DATABASE_ERROR = '2d464394-88e6-4e25-9603-a30d55230993';

    protected static $errorNames = [
        self::MYSQL_CONNECTION_ERROR => 'MYSQL_CONNECTION_ERROR',
        self::MYSQL_DATABASE_ERROR => 'MYSQL_DATABASE_ERROR',
    ];

    public $message = 'The MySQL Connection Settings did not connect. [{message}]';

    public $db_msg = 'The database does not exist and cannot be created. [{message}]';

    public $transDomain = 'System';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}