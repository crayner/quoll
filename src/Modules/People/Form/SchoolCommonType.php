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
 * Date: 18/07/2020
 * Time: 11:44
 */
namespace App\Modules\People\Form;

use App\Form\Type\HeaderType;
use App\Form\Type\HiddenEntityType;
use App\Form\Type\ReactFileType;
use App\Form\Type\ReactFormType;
use App\Form\Type\ToggleType;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\House;
use App\Modules\System\Entity\I18n;
use App\Provider\ProviderFactory;
use App\Util\ParameterBagHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class StaffType
 * @package App\Modules\Staff\Form
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SchoolCommonType extends AbstractType
{
    /**
     * buildForm
     * @param FormBuilderInterface $builder
     * @param array $options
     * 18/07/2020 11:47
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $options['data']->getPerson();
        $locale = ProviderFactory::getRepository(I18n::class)->findOneByCode(ParameterBagHelper::get('locale'));
        $builder
            ->add('person', HiddenEntityType::class,
                [
                    'class' => Person::class,
                ]
            )
            ->add('schoolHeader', HeaderType::class,
                [
                    'label' => 'School Details',
                ]
            )
            ->add('dateStart', DateType::class,
                [
                    'label' => 'Start Date',
                    'help' => 'First date at this school.',
                    'input' => 'datetime_immutable'
                ]
            )
            ->add('dateEnd', DateType::class,
                [
                    'label' => 'End Date',
                    'help' => 'Last date at this school.',
                    'input' => 'datetime_immutable'
                ]
            )
            ->add('viewCalendarPersonal', ToggleType::class,
                [
                    'label' => 'View School Calendar Details',
                    'visible_by_choice' => 'personal_calendar'
                ]
            )
            ->add('calendarFeedPersonal', TextType::class,
                [
                    'label' => 'Personal Calendar Feed',
                    'help' => 'Use as a Google Calendar feed merge into your personal school calendar.',
                    'visible_parent' => 'school_' . $options['person_type'] . '_viewCalendarPersonal',
                    'visible_values' => ['personal_calendar'],
                ]
            )
            ->add('viewCalendarSchool', ToggleType::class,
                [
                    'label' => 'View School Calendar Details',
                    'visible_parent' => 'school_' . $options['person_type'] . '_viewCalendarPersonal',
                    'visible_values' => ['personal_calendar'],
                ]
            )
            ->add('lockerNumber', TextType::class,
                [
                    'label' => 'Locker Number',
                ]
            )
            ->add('house', EntityType::class,
                [
                    'label' => 'House',
                    'class' => House::class,
                    'placeholder' => 'Please select...',
                ]
            )
            ->add('personalBackground', ReactFileType::class,
                [
                    'label' => 'Personal Background Image',
                    'help' => 'Max size of 1.5MB with a landscape ratio between 16:9 and 5:4',
                    'file_prefix' => 'Background_' . str_replace([' ', ',','`',"'"], '_',$person->getSurname()),
                    'show_thumbnail' => true,
                    'data' => $options['data']->getPersonalBackground(),
                    'image_method' => 'getPersonalBackground',
                    'entity' => $options['data'],
                    'delete_route' => $options['remove_personal_background'],
                ]
            )
            ->add('messengerLastBubble', DateType::class,
                [
                    'label' => 'Last Messenger Bubble Date',
                    'input' => 'datetime_immutable'
                ]
            )
            ->add('receiveNotificationEmails', ToggleType::class,
                [
                    'label' => 'Receive Notification EMails',
                ]
            )
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * getParent
     * @return string|null
     * 18/07/2020 11:45
     */
    public function getParent()
    {
        return ReactFormType::class;
    }
}
