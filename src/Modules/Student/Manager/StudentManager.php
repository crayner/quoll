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
 * Date: 20/06/2020
 * Time: 11:31
 */
namespace App\Modules\Student\Manager;

use App\Modules\Behaviour\Entity\Behaviour;
use App\Modules\IndividualNeed\Entity\INPersonDescriptor;
use App\Modules\MarkBook\Entity\MarkBookEntry;
use App\Modules\Medical\Entity\PersonMedical;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AlertLevel;
use App\Modules\School\Util\AcademicYearHelper;
use App\Modules\Security\Voter\StudentProfileVoter;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentManager
{
    /**
     * getAlertBar
     * @param Person $student
     * @param string $divExtras
     * @param bool $div
     * @param bool $large
     * @return array
     * 20/06/2020 12:50
     */
    public static function getAlertBar(Person $student, string $divExtras = '', bool $div = true, bool $large = false)
    {
        $output = '';
        $alerts = [];
        $privacy = $student->getPrivacy();

        if (StudentProfileVoter::getStudentProfileAccess() === 'Staff' && StudentProfileVoter::isStudentAccessible($student)) {

            // Individual Needs
            $in_alerts = ProviderFactory::getRepository(INPersonDescriptor::class)->findAlertsByPerson($student) ?: [];

            if (count($in_alerts) > 0) {
                $alert = reset($in_alerts);
                $alerts[] = self::resolveAlert(
                    [
                        'highestLevel'    => $alert['name'],
                        'highestColour'   => $alert['color'],
                        'highestColourBG' => $alert['colorBG'],
                        'tag'             => 'IN',
                        'title'           => 'in_alert_level',
                        'title_params'    => ['%count%' => count($in_alerts), '%name%' => $in_alerts[0]->getName()],
                        'link'            => './?q=/modules/Students/student_view_details.php&gibbonPersonID='.$student->getId().'&subpage=Individual Needs',
                    ]
                );
            }

            // Academic
            $alertName = '';
            $alertThresholdText = '';

            $results = ProviderFactory::getRepository(MarkBookEntry::class)->findAttainmentOrEffortConcerns($student, AcademicYearHelper::getCurrentAcademicYear());

            $settingProvider = SettingFactory::getSettingManager();
            $academicAlertLowThreshold = $settingProvider->get('Students', 'academicAlertLowThreshold');
            $academicAlertMediumThreshold = $settingProvider->get('Students', 'academicAlertMediumThreshold');
            $academicAlertHighThreshold = $settingProvider->get('Students', 'academicAlertHighThreshold');

            if (count($results) >= $academicAlertHighThreshold) {
                $alertName = 'Low';
                $alertThresholdParams = ['low' => $academicAlertHighThreshold];
            } elseif (count($results) >= $academicAlertMediumThreshold) {
                $alertName = 'Medium';
                $alertThresholdParams = ['high' => $academicAlertHighThreshold - 1, 'low' => $academicAlertMediumThreshold];
            } elseif (count($results) >= $academicAlertLowThreshold) {
                $alertName = "High";
                $alertThresholdParams = ['high' => $academicAlertMediumThreshold - 1, 'low' => $academicAlertLowThreshold];
            }
            if ($alertName !== '') {
                if ($alert = ProviderFactory::getRepository(AlertLevel::class)->findByName($alertName)) {
                    $alerts[] = self::resolveAlert([
                        'highestLevel'    => $alert->getName(),
                        'highestColour'   => $alert->getColour(),
                        'highestColourBG' => $alert->getColourBG(),
                        'tag'             => 'A',
                        'title'           => 'concerns_alert_level', // 'Student has a %name% alert for academic concern over the past 60 days.',
                        'title_params'    => array_merge(['name' => $alert->getName(), 'highest_level' => $alert->getName()],  $alertThresholdParams),
                        'translation_domain'    => 'kookaburra',
                        'link'            => './?q=/modules/Students/student_view_details.php&gibbonPersonID='.$student->getId().'&subpage=MarkBook&filter='.AcademicYearHelper::getCurrentAcademicYear()->getId(),
                    ]);
                }
            }

            // Behaviour
            $alertName = '';
            $alertThresholdText = '';

            $results = ProviderFactory::getRepository(Behaviour::class)->findNegativeInLast60Days($student);

            $behaviourAlertLowThreshold = $settingProvider->get('Students', 'behaviourAlertLowThreshold');
            $behaviourAlertMediumThreshold = $settingProvider->get('Students', 'behaviourAlertMediumThreshold');
            $behaviourAlertHighThreshold = $settingProvider->get('Students', 'behaviourAlertHighThreshold');

            if (count($results) >= $behaviourAlertHighThreshold) {
                $alertName = 'Low';
                $alertThresholdParams = ['low' => $behaviourAlertHighThreshold];
            } elseif (count($results) >= $behaviourAlertMediumThreshold) {
                $alertName = 'Medium';
                $alertThresholdParams = ['high' => $behaviourAlertHighThreshold - 1, 'low' => $behaviourAlertMediumThreshold];
            } elseif (count($results) >= $behaviourAlertLowThreshold) {
                $alertName = 'High';
                $alertThresholdParams = ['high' => $behaviourAlertMediumThreshold - 1, 'low' => $behaviourAlertLowThreshold];
            }

            if ($alertName !== '') {
                if ($alert = ProviderFactory::getRepository(AlertLevel::class)->findByName($alertName)) {
                    $alerts[] = self::resolveAlert([
                        'highestLevel'    => $alert->getName(),
                        'highestColour'   => $alert->getColour(),
                        'highestColourBG' => $alert->getColourBG(),
                        'tag'             => 'B',
                        'title'           => 'behaviour_alert_level', // 'Student has a %name% alert for academic concern over the past 60 days.',
                        'title_params'    => array_merge(['name' => $alert->getName(), 'highest_level' => $alert->getName()],  $alertThresholdParams),
                        'link'            => './?q=/modules/Students/student_view_details.php&gibbonPersonID='.$student->getId().'&subpage=Behaviour',
                        'translation_domain' => 'messages',
                    ]);
                }
            }

            // Medical
            if ($alert = ProviderFactory::getRepository(PersonMedical::class)->findHighestMedicalRisk($student)) {
                $alerts[] = self::resolveAlert([
                    'highestLevel'    => $alert[1],
                    'highestColour'   => $alert[3],
                    'highestColourBG' => $alert[4],
                    'tag'             => 'M',
                    'title'           => 'medical_alert_level',
                    'title_params'    => ['name' => $alert->getName()],
                    'translation_domain' => 'messages',
                    'link'            => './?q=/modules/Students/student_view_details.php&gibbonPersonID='.$student->getId().'&subpage=Medical',
                ]);
            }

            // Privacy
            $privacySetting = $settingProvider->get('People', 'privacy');
            if ($privacySetting && $privacy !== '' && null !== $privacy) {
                if ($alert = ProviderFactory::getRepository(AlertLevel::class)->find(1)) {
                    $alerts[] = self::resolveAlert([
                        'highestLevel'    => $alert->getName(),
                        'highestColour'   => $alert->getColour(),
                        'highestColourBG' => $alert->getColourBG(),
                        'tag'             => 'P',
                        'title'           => 'privacy_alert_level', // sprintf(__('Privacy is required: {oneString}'), $privacy),
                        'title_params'    => ['message' => $privacy],
                        'translation_domain' => 'messages',
                        'link'            => './?q=/modules/Students/student_view_details.php&gibbonPersonID='.$student->getId(),
                    ]);
                }
            }

            // Output alerts

            $alerts['alerts'] = $alerts;
            $alerts['classDefault'] = 'block align-middle text-center font-bold border-0 border-t-2 ';
            $alerts['classDefault'] .= $large
                ? 'text-4xl w-10 pt-1 mr-2 leading-none'
                : 'text-xs w-4 pt-px mr-1 leading-none';

            if ($div) {
                $alerts['wrapperClass'] =  'w-20 lg:w-24 h-6 text-left py-1 px-0 mx-auto';
                $alerts['wrapper'] = true;
                $alerts['wrapperExtras'] = $divExtras;
            }
        }

        return $alerts;
    }

    /**
     * resolveAlert
     * @param array $alert
     * @return array
     * 20/06/2020 12:57
     */
    private static function resolveAlert(array $alert)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'highestLevel',
                'highestColour',
                'highestColourBG',
                'tag',
                'title',
                'link',
            ]
        );
        $resolver->setDefaults(
            [
                'title_params' => [],
                'translation_domain' => 'messages',
            ]
        );
        $resolver->addAllowedValues('highestLevel', ['High', 'Medium', 'Low']);
        $resolver->setAllowedTypes('tag', ['string']);
        $resolver->setAllowedTypes('highestColour', ['string']);
        $resolver->setAllowedTypes('highestColourBG', ['string']);
        $resolver->setAllowedTypes('title', ['string']);
        $resolver->setAllowedTypes('link', ['string']);
        return $resolver->resolve($alert);
    }

    /**
     * getStudentsOfStaff
     * @param Person $staff
     * @return array
     * 22/06/2020 11:14
     */
    public static function getStudentsOfStaff(Person $staff): array
    {
        if ($staff->isSystemAdmin() || $staff->isRegistrar() || $staff->isPrincipal()) {
            return ProviderFactory::getRepository(Person::class)->findAllStudents();
        }
        return [];
    }
}