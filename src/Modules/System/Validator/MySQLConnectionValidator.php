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
 * Time: 14:26
 */
namespace App\Modules\System\Validator;

use App\Modules\System\Form\Entity\MySQLSettings;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class MySQLConnectionValidator
 * @package App\Validator
 */
class MySQLConnectionValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof MySQLSettings) {
            return;
        }

        try {
            $driver = new Driver();
            $conn = $driver->connect($value->getParams(false), $value->getUser(), $value->getPassword(), []);
        } catch(DriverException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{message}', $e->getMessage())
                ->setCode(MySQLConnection::MYSQL_CONNECTION_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;
        }

        $sql = "CREATE DATABASE IF NOT EXISTS " . $value->getDbname() . " DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci";
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            $this->context->buildViolation($constraint->db_msg)
                ->setParameter('{message}', $e->getMessage())
                ->setCode(MySQLConnection::MYSQL_DATABASE_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
            return;
        }

        try {
            $driver = new Driver();
            $conn = $driver->connect($value->getParams(), $value->getUser(), $value->getPassword(), []);
        } catch(DriverException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{message}', $e->getMessage())
                ->setCode(MySQLConnection::MYSQL_CONNECTION_ERROR)
                ->setTranslationDomain($constraint->transDomain)
                ->addViolation();
        }
    }
}