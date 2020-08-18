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
use App\Manager\StatusManager;
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
        'residentialAddress'     => ['label' => 'Residential Address', 'default' => 'required'],
        'postalAddress'          => ['label' => 'Postal Address', 'default' => ''],
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
        'emergency1Contact'      => ['label' => '1st Emergency Contact', 'default' => ''],
        'emergency1Phone1'       => ['label' => '1st Emergency Contact Personal Phone', 'default' => ''],
        'emergency1Phone2'       => ['label' => '1st Emergency Contact Additional Phone', 'default' => ''],
        'emergency1Relationship' => ['label' => '1st Emergency Contact Relationship', 'default' => ''],
        'emergency2Contact'      => ['label' => '2nd Emergency Contact', 'default' => ''],
        'emergency2Phone1'       => ['label' => '2nd Emergency Contact Personal Phone', 'default' => ''],
        'emergency2Phone2'       => ['label' => '2nd Emergency Contact Additional Phone', 'default' => ''],
        'emergency2Relationship' => ['label' => '2nd Emergency Contact Relationship', 'default' => ''],
    ];

    /**
     * @var array
     */
    private $settings;

    /**
     * @var StatusManager
     */
    private StatusManager $statusManager;

    /**
     * RequiredUpdates constructor.
     * @param StatusManager $statusManager
     */
    public function __construct(StatusManager $statusManager)
    {
        $this->statusManager = $statusManager;
        $this->settings = SettingFactory::getSettingManager()->get( 'People', 'personalDataUpdaterRequiredFields');
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
            $this->getStatusManager()->success();
        } catch (InvalidOptionsException $e) {
            $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
        }
        if ($this->getStatusManager()->isStatusSuccess()) {
            foreach ($updaterData as $q => $w) {
                $resolver->clear();
                $resolver->setRequired(array_keys($this->getSettingDefaults(false)));
                try {
                    $updaterData[$q] = $resolver->resolve($w);
                } catch (InvalidOptionsException $e) {
                    $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
                }

                foreach ($updaterData[$q] as $e => $r) {
                    if (!in_array($r, $this->getOptions())) {
                        $this->getStatusManager()->error(StatusManager::INVALID_INPUTS);
                    }
                    if ($r === '') {
                        $updaterData[$q][$e] = null;
                    }
                }
            }

            $this->setSettings($updaterData);
            SettingFactory::getSettingManager()->set('People', 'personalDataUpdaterRequiredFields', $this->getSettings());
        }
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

    /**
     * @return StatusManager
     */
    public function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }

}
