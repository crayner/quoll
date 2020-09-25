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
 * Date: 20/08/2019
 * Time: 09:33
 */

namespace App\Modules\People\Form;

use App\Modules\People\Form\Subscriber\PreferenceStaffSubscriber;
use App\Modules\People\Util\UserHelper;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Staff\Entity\Staff;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Manager\SettingFactory;
use App\Modules\System\Entity\Theme;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use App\Modules\People\Entity\Person;
use App\Form\Transform\ToggleTransformer;
use App\Form\Type\HeaderType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class PreferenceSettingsType
 * @package App\Modules\People\Form
 */
class PreferenceType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settingHeader', HeaderType::class,
                [
                    'label' => 'Settings',
                ]
            )
        ;
        if ($options['data'] instanceof Person && $options['data']->isStaff()) {
            $builder
                ->add('staff', PreferenceStaffType::class,
                    [
                        'data' => $options['data']->getStaff(),
                        'remove_background_image' => $options['remove_background_image'],
                    ]
                )
                ->add('securityUser', PreferenceSecurityType::class, ['data' => $options['data']->getSecurityUser()])
            ;
        }
        if ($options['data'] instanceof Person && $options['data']->isStudent()) {
            $builder
                ->add('student', PreferenceStudentType::class,
                    [
                        'data' => $options['data']->getStudent(),
                        'remove_background_image' => $options['remove_background_image'],
                    ]
                )
                ->add('securityUser', PreferenceSecurityType::class, ['data' => $options['data']->getSecurityUser()])
            ;
        }
        if ($options['data'] instanceof Person && $options['data']->getSecurityUser()->isAllowedYearSwitch()) {
            $su = $options['data']->getSecurityUser();
            $yearTypes = ['Current'];
            if ($su->isAllowedFutureYears()) $yearTypes[] = 'Upcoming';
            if ($su->isAllowedPastYears()) $yearTypes[] = 'Past';
            $data = AcademicYearHelper::getCurrentAcademicYear()->getStatus() === 'Current' ? null : AcademicYearHelper::getCurrentAcademicYear(true);
            $builder->add('academicYear', EntityType::class,
                    [
                        'label' => 'Work in another Academic Year',
                        'class' => AcademicYear::class,
                        'choice_label' => 'name',
                        'placeholder' => 'Work in the current Academic Year',
                        'data' => $data,
                        'query_builder' => function (EntityRepository $er) use ($yearTypes) {
                            return $er->createQueryBuilder('ay')
                                ->orderBy('ay.firstDay','ASC')
                                ->addOrderBy('ay.name', 'ASC')
                                ->where('ay.status in (:status)')
                                ->setParameter('status', $yearTypes, Connection::PARAM_STR_ARRAY)
                            ;
                        },
                        'mapped' => false,
                    ]
                )
            ;

        }
        $builder
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * configureOptions
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Person::class,
                'translation_domain' => 'People',
                'attr' => [
                    'className' => 'smallIntBorder fullWidth standardForm',
                    'autoComplete' => 'on',
                ],
            ]
        );
        $resolver->setRequired(
            [
                'remove_background_image',
            ]
        );
    }

    /**
     * getParent
     * @return string|null
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}