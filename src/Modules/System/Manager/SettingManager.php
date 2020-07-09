<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 1/07/2019
 * Time: 10:27
 */
namespace App\Modules\System\Manager;

use App\Modules\People\Entity\Person;
use App\Modules\System\Exception\SettingNotFoundException;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SettingProvider
 * @package App\Modules\System\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingManager
{
    /**
     * @var ArrayCollection
     */
    private $settings;

    /**
     * @var bool
     */
    private $settingsChanged = false;

    /**
     * SettingProvider constructor.
     * @param array $settings
     */
    public function __construct(?array $settings)
    {
        if ($settings === null) {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists(__DIR__ . '/../../../../config/packages/settings.yaml')) {
                $fileSystem->copy(__DIR__ . '/../../../../config/packages/settings.yaml.dist',__DIR__ . '/../../../../config/packages/settings.yaml');
                $settings = Yaml::parse(__DIR__ . '/../../../../config/packages/settings.yaml');
                $settings = $settings['parameters']['settings'];
            }
        }
        $this->settings = $this->convertRawSettings($settings);
    }

    /**
     * convertRawSettings
     * @param array $settings
     * @return ArrayCollection
     * 5/07/2020 12:34
     */
    private function convertRawSettings(array $settings): ArrayCollection
    {
        $result = new ArrayCollection();
        foreach($settings as $scope=>$items){
            $scopeItems = new ArrayCollection();
            foreach($items as $name=>$valueType) {
                $scopeItems->set($name, $valueType);
            }
            $result->set($scope, $scopeItems);
        }
        return $result;
    }

    /**
     * getSettingByScope
     * @param string $scope
     * @param string $name
     * @param bool $returnRow
     * @throws \Exception
     * @deprecated Move this to getSetting.  Lots of work to do here...
     * 10/06/2020 10:47
     */
    public function getSettingByScope(string $scope, string $name, $returnRow = false)
    {
        return $this->getSetting($scope, $name);
    }


    /**
     * getSettingsByScope
     * @param string $scope
     * @return ArrayCollection|null
     * 5/07/2020 12:01
     */
    public function getSettingsByScope(string $scope): ?ArrayCollection
    {
        return $this->getSettings()->containsKey($scope) ? $this->getSettings()->get($scope) : null;
    }

    /**
     * getSystemSettings
     * @throws \Exception
     */
    public function getSystemSettings()
    {
        //System settings
        $result = $this->getSettingsByScope('System');
    }

    /**
     * getSettingByScopeAsInteger
     * @param string $scope
     * @param string $name
     * @param int $default
     * @return int
     * @throws \Exception
     * @deprecated Use getSetting
     */
    public function getSettingByScopeAsInteger(string $scope, string $name, int $default = 0): int
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result))
            return $default;
        return intval($result);
    }

    /**
     * getSettingByScopeAsArray
     * @param string $scope
     * @param string $name
     * @param array $default
     * @return array
     * @throws \Exception
     * @deprecated Use getSetting
     */
    public function getSettingByScopeAsArray(string $scope, string$name, array $default = []): array
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result))
            return $default;

        $x = @unserialize($result);
        if (is_array($x))
            $ok = true;
        else
            $ok = false;

        if ($ok)
            return $x;

        return explode(',', $result);
    }

    /**
     * getSettingByScopeAsEntity
     * Assumes a single ID filed for the entity
     * @param string $scope
     * @param string $name
     * @deprecated Use getSetting
     * @param string $entityName
     * @return EntityInterface|null
     * 1/06/2020 09:16
     */
    public function getSettingByScopeAsEntity(string $scope, string$name, string $entityName): ?EntityInterface
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result)) {
            return null;
        }

        try {
            return $this->getRepository($entityName)->find($result->getvalue);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * getSettingByScopeAsArray
     * @param string $scope
     * @param string $name
     * @param array $default
     * @return array
     * @deprecated Use getSetting
     * @throws \Exception
     */
    public function getSettingByScopeAsDate(string $scope, string $name, ?\DateTime $default = null)
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result))
            return $default;
        return unserialize($result);
    }

    /**
     * getSettingByScopeAsBoolean
     * @param string $scope
     * @param string $name
     * @param bool|null $default
     * @return bool|null
     * @deprecated Use getSetting
     * @throws \Exception
     */
    public function getSettingByScopeAsBoolean(string $scope, string $name, ?bool $default = false)
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result))
            return $default;
        return $result === 'Y' ? true : false ;
    }

    /**
     * getSettingByScopeAsString
     * @param string $scope
     * @param string $name
     * @param string|null $default
     * @return string|null
     * @deprecated Use getSetting
     * @throws \Exception
     */
    public function getSettingByScopeAsString(string $scope, string $name, ?string $default = null)
    {
        $result = $this->getSettingByScope($scope, $name);
        if (empty($result))
            return $default;
        return strval($result);
    }

    /**
     * setSettingByScope
     * @param string $scope
     * @param string $name
     * @param string $value
     * @throws SettingNotFoundException
     * @deprecated Use setSetting
     */
    public function setSettingByScope(string $scope, string $name, $value): self
    {
        return $this->setSetting($scope,$name,$value);
    }

    /**
     * setSettingByScope
     * @param string $scope
     * @param string $name
     * @param string $value
     * @throws SettingNotFoundException
     * @throws \Exception
     */
    public function setSetting(string $scope, string $name, $value): self
    {
        if (!$this->hasSetting($scope,$name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);

        switch ($setting['type']) {
            case 'App\Modules\People\Entity\Person':
                $value = $value ? $value->getId() : null;
                if ($setting['value'] !== $value) {
                    $setting['value'] = $value;
                    $this->setSettingsChanged();
                }
                break;
            case 'string':
                if (is_null($value) || is_string($value))
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                break;
            case 'integer':
                if (is_null($value) || is_int($value))
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                break;
            default:
                throw new \Exception(sprintf('How do I save a %s', $setting['type']));
        }

        $this->getSettings()->get($scope)->set($name, $setting);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSettings(): ArrayCollection
    {
        return $this->settings;
    }

    /**
     * Settings.
     *
     * @param ArrayCollection $settings
     * @return SettingManager
     */
    public function setSettings(ArrayCollection $settings): SettingManager
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * addSetting
     * @param Setting $setting
     * @return SettingManager
     */
    private function addSetting(Setting $setting): SettingManager
    {
        $scope = $setting->getScope();
        $name = $setting->getName();
        if (!$this->getSettings()->containsKey($scope))
            $this->settings->set($scope, new ArrayCollection());

        $this->settings->get($scope)->set($name, $setting);
        return $this;
    }

    /**
     * hasSetting
     * @param string $scope
     * @param string $name
     * @param bool $isEmpty
     * @return bool
     * 5/07/2020 17:46
     * @throws \Exception
     */
    public function hasSetting(string $scope, string $name, bool $isEmpty = false): bool
    {
        if ($isEmpty) {
            if ($this->getSettings()->containsKey($scope) && $this->getSettings()->get($scope)->containsKey($name)) {
                $w = $this->getSettings()->get($scope)->get($name);
                switch ($w['type']) {
                    case 'string':
                        return !in_array($w['value'], [null, '']);
                        break;
                    default:
                        throw new \Exception('Missing Setting Type work for '.$w['type']);
                }
            }
        } else {
            return $this->getSettings()->containsKey($scope) && $this->getSettings()->get($scope)->containsKey($name);
        }
    }

    /**
     * getSetting
     * @param $scope
     * @param $name
     * @param null $default
     * @return array|bool|int|string|null
     * @throws \Exception
     * 9/07/2020 10:35
     */
    public function getSetting($scope, $name, $default = null)
    {
        if (!$this->hasSetting($scope, $name)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not have a setting named "%s".', $scope, $name));
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        $value = null;
        switch ($setting['type']) {
            case 'string':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?? $default;
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid string.', $scope,$name));
                }
                break;
            case 'boolean':
                if (is_bool($setting['value'])) {
                    $value = $setting['value'] ?? $default;
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not boolean.', $scope,$name));
                }
                break;
            case 'integer':
                if (is_null($setting['value']) || is_integer($setting['value'])) {
                    $value = $setting['value'] ?? $default;
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid integer.', $scope,$name));
                }
                break;
            case 'array':
                if (is_null($setting['value']) || is_array($setting['value'])) {
                    $value = $setting['value'] ?? ($default ?? []);
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid array.', $scope,$name));
                }
                break;
            case 'image':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?? $default;
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid image.', $scope,$name));
                }
                if ($value !== null && $value !== '') {
                    if (!is_file(__DIR__ . '/../../../../public/' . ltrim($setting['value'], '/'))) {
                        $value = $default;
                    }
                }
                break;
            case 'entity':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?? $default;
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid person.', $scope,$name));
                }
                if (is_string($value)) {
                    $value = ProviderFactory::getRepository($setting['class'])->find($value);
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Please write code to handle %s', $setting['type']));
        }
        return $value;
    }

    /**
     * getSettingType
     * @param $scope
     * @param $name
     * @return mixed
     * @throws \Exception
     * 9/07/2020 10:35
     */
    public function getSettingType($scope, $name)
    {
        if (!$this->hasSetting($scope, $name)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not have a setting named "%s".', $scope, $name));
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        return $setting['type'];
    }

    /**
     * getSettingClass
     * @param $scope
     * @param $name
     * @return mixed
     * @throws \Exception
     * 9/07/2020 13:03
     */
    public function getSettingClass($scope, $name)
    {
        if (!$this->hasSetting($scope, $name)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not have a setting named "%s".', $scope, $name));
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        return $setting['class'];
    }

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors = $this->errors ?: [];
    }

    /**
     * Errors.
     *
     * @param array $error
     * @return SettingManager
     */
    public function addError(array $error): SettingManager
    {
        $this->getErrors();
        $this->errors[] = $error;
        return $this;
    }

    /**
     * handleSettingsForm
     * @param FormInterface $form
     * @param Request $request
     * @param array|null $data
     * @return array
     */
    public function handleSettingsForm(FormInterface $form, Request $request, ?array $data = null): array
    {
        $content = json_decode($request->getContent(), true);

        $form->submit($content);

        if ($form->isValid()) {
            $this->saveSettings($form, $content);
            if (count($this->getErrors()) === 0) {
                if (is_array($data)) {
                    return ErrorMessageHelper::getSuccessMessage($data, true);
                }
                return $this->addError(['class' => 'success', 'message' => ErrorMessageHelper::onlySuccessMessage(true)])->getErrors();
            }
            else {
                if (is_array($data)) {
                    $data['errors'] = $this->getErrors();
                    $data['status'] = $this->getStatus();
                    return $data;
                }
                return $this->getErrors();
            }
        }

        foreach($form->getErrors(true) as $error)
        {
            $this->addError(['class' => 'error', 'message' => $error->getOrigin()->getName() . ': ' . $error->getMessage()]);
        }

        if (is_array($data)) {
            $data['errors'] = $this->getErrors();
            $data['status'] = $this->getStatus();
            return $data;
        }

        return $this->addError(['class' => 'error', 'message' => ErrorMessageHelper::onlyInvalidInputsMessage(true)])->getErrors();
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        $status = 'success';
        foreach($this->getErrors() as $error)
            if ($error['class'] === 'error') {
                $status = 'error';
                break;
            }
        if ($error['class'] === 'warning') {
            $status = 'warning';
        }
        return $status;
    }

    /**
     * @return bool
     */
    public function isSettingsChanged(): bool
    {
        return $this->settingsChanged;
    }

    /**
     * @param bool $settingsChanged
     * @return SettingManager
     */
    public function setSettingsChanged(bool $settingsChanged = true): SettingManager
    {
        $this->settingsChanged = $settingsChanged;
        return $this;
    }

    /**
     * writeSettings
     * 6/07/2020 07:52
     */
    public function writeSettings()
    {
        if ($this->isSettingsChanged() && $this->getSettings()->count() > 0) {
            $settings = [];
            foreach($this->getSettings() as $q=>$w) {
                foreach($w as $a=>$b) {
                    $settings[$q][$a] = $b;
                }
            }

            file_put_contents(__DIR__ . '/../../../../config/packages/settings.yaml', Yaml::dump(['parameters' => ['settings' => $settings]], 8));
        }
    }
}
