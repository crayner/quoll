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
 * Date: 12/11/2020
 * Time: 09:50
 */
namespace App\Modules\Enrolment\Validator;

use App\Modules\Enrolment\Entity\CourseClass;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CourseClassAccessValidator
 *
 * 12/11/2020 09:51
 * @package App\Modules\Enrolment\Validator
 * @author Craig Rayner <craig@craigrayner.com>
 */
class CourseClassAccessValidator extends ConstraintValidator
{
    /**
     * @var Security
     */
    private Security $security;

    /**
     * CourseClassAccessValidator constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * validate
     *
     * 12/11/2020 09:52
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof CourseClass) return;

        if (!$this->getSecurity()->isGranted('ROLE_COURSE_CLASS', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->translationDomain)
                ->setCode(CourseClassAccess::COURSE_CLASS_ACCESS_ERROR)
                ->addViolation();
        }
    }

    /**
     * Security
     *
     * @return Security
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }

}
