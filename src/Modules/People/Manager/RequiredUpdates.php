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
 * Date: 3/12/2019
 * Time: 21:27
 */
namespace App\Modules\People\Manager;

use App\Manager\SpecialInterface;
use App\Modules\System\Manager\SettingFactory;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\TranslationHelper;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RequiredUpdates
 * @package App\Modules\People\Manager
 */
class RequiredUpdates implements SpecialInterface {
    /**
     * @var array 
     */
    private $settingDefaults = [
        'title'                  => ['label' => 'Title', 'default' => 'required'],
        'surname'                => ['label' => 'Surname', 'default' => 'required'],
        'firstName'              => ['label' => 'First Name', 'default' => ''],
        'preferredName'          => ['label' => 'Preferred Name', 'default' => 'required'],
        'officialName'           => ['label' => 'Official Name', 'default' => 'required'],
        'nameInCharacters'       => ['label' => 'Name In Characters', 'default' => ''],
        'dob'                    => ['label' => 'Date of Birth', 'default' => ''],
        'email'                  => ['label' => 'Email', 'default' => ''],
        'emailAlternate'         => ['label' => 'Alternate Email', 'default' => ''],
        'address1'               => ['label' => 'Residential Address', 'default' => 'required'],
        'address2'               => ['label' => 'Postal Address', 'default' => ''],
        'phone1'                 => ['label' => 'Phone 1', 'default' => ''],
        'phone2'                 => ['label' => 'Phone 2', 'default' => ''],
        'phone3'                 => ['label' => 'Phone 3', 'default' => ''],
        'phone4'                 => ['label' => 'Phone 4', 'default' => ''],
        'languageFirst'          => ['label' => 'First Language', 'default' => ''],
        'languageSecond'         => ['label' => 'Second Language', 'default' => ''],
        'languageThird'          => ['label' => 'Third Language', 'default' => ''],
        'countryOfBirth'         => ['label' => 'Country of Birth', 'default' => ''],
        'ethnicity'              => ['label' => 'Ethnicity', 'default' => ''],
        'religion'               => ['label' => 'Religion', 'default' => ''],
        'citizenship1'           => ['label' => 'Citizenship 1', 'default' => ''],
        'citizenship1Passport'   => ['label' => 'Citizenship 1 Passport', 'default' => ''],
        'citizenship2'           => ['label' => 'Citizenship 2', 'default' => ''],
        'citizenship2Passport'   => ['label' => 'Citizenship 2 Passport', 'default' => ''],
        'nationalIDCardNumber'   => ['label' => 'National ID Card Number', 'default' => ''],
        'residencyStatus'        => ['label' => 'Residency Status', 'default' => ''],
        'visaExpiryDate'         => ['label' => 'Visa Expiry Date', 'default' => ''],
        'profession'             => ['label' => 'Profession', 'default' => ''],
        'employer'               => ['label' => 'Employer', 'default' => ''],
        'jobTitle'               => ['label' => 'Job Title', 'default' => ''],
        'vehicleRegistration'    => ['label' => 'Vehicle Registration', 'default' => ''],
        'emergencyContact1'      => ['label' => 'Emergency Contact 1', 'default' => ''],
        'emergencyContact2'      => ['label' => 'Emergency Contact 2', 'default' => ''],
    ];

    /**
     * @var array
     */
    private $settings;

    /**
     * RequiredUpdates constructor.
     * @param array $settings
     */
    public function __construct()
    {
        $this->settings = SettingFactory::getSettingManager()->getSettingByScopeAsArray( 'People', 'personalDataUpdaterRequiredFields');
    }


    private $options = [
        ''         => '',
        'required' => 'Required',
        'readonly' => 'Read Only',
        'hidden'   => 'Hidden',
    ];

    /**
     * @param bool $fixed
     * @return array
     */
    public function getSettingDefaults(bool $fixed = true): array
    {
        if ($fixed)
            return $this->settingDefaults;

        $result = [];
        foreach($this->settingDefaults as $q=>$w)
        {
            if ($w['default'] !== 'fixed')
                $result[$q] = $w;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * getSetting
     * @param string $name
     * @return array
     */
    public function getSetting(string $name): array
    {
        return $this->getSettings()[$name];
    }

    /**
     * Settings.
     *
     * @param array $settings
     * @return RequiredUpdates
     */
    public function setSettings(array $settings): RequiredUpdates
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return array_flip($this->options);
    }

    /**
     * handleRequest
     * @param array $updaterData
     */
    public function handleRequest(array $updaterData): array
    {
        $resolver = new OptionsResolver();
        $data = [];
        $resolver->setRequired(
            [
                'Staff',
                'Student',
                'Parent',
                'Other',
            ]
        );

        try {
            $updaterData = $resolver->resolve($updaterData);
        } catch (InvalidOptionsException $e) {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data);
        }
        foreach($updaterData as $q=>$w)
        {
            $resolver->clear();
            $resolver->setRequired(array_keys($this->getSettingDefaults(false)));
            try {
                $updaterData[$q] = $resolver->resolve($w);
            } catch (InvalidOptionsException $e) {
                $data = ErrorMessageHelper::getInvalidInputsMessage($data);
            }

            foreach($updaterData[$q] as $e=>$r) {
                if (!in_array($r, $this->getOptions()))
                    $data = ErrorMessageHelper::getInvalidInputsMessage($data);
            }
        }

        $this->setSettings($updaterData);
        SettingFactory::getSettingManager()->setSettingByScope('People', 'personalDataUpdaterRequiredFields', serialize($this->getSettings()));
        return $data;
    }

    /**
     * toArray
     * @return array
     */
    public function toArray(): array
    {
        TranslationHelper::setDomain('People');
        return [
            'settings' => $this->getSettingChoices(),
            'name' => $this->getName(),
            'messages' => [
                'Parent' => TranslationHelper::translate('Parent'),
                'Staff' => TranslationHelper::translate('Staff'),
                'Student' => TranslationHelper::translate('Student'),
                'Other' => TranslationHelper::translate('Other'),
                'required_fields_header' => TranslationHelper::translate('required_fields_header'),
                'required_fields_help' => TranslationHelper::translate('required_fields_help'),
                'never_required' => TranslationHelper::translate('never_required'),
                'Field' => TranslationHelper::translate('Field'),
                'required' => TranslationHelper::translate('Required'),
                'read_only' => TranslationHelper::translate('read_only'),
                'hidden' => TranslationHelper::translate('Hidden'),
                'submit' => TranslationHelper::translate('Submit', [], 'messages'),
            ],
        ];
    }

    /**
     * getName
     * @return string
     */
    public function getName(): string
    {
        return 'required_data_updates';
    }

    /**
     * getSettingChoices
     * @return array
     */
    private function getSettingChoices()
    {
        $result = [];
        foreach($this->settings as $group=>$values) {
            foreach($values as $name=>$value) {
                $label = $this->settingDefaults[$name]['label'];
                $result[$group][$name] = new ChoiceView($value, $value ?? $this->settingDefaults[$name]['default'], TranslationHelper::translate($label));
            }
        }
        return $result;
    }
}