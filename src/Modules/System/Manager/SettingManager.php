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

use App\Modules\System\Exception\SettingNotFoundException;
use App\Modules\System\Form\SettingsType;
use App\Util\ErrorMessageHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * SettingProvider constructor.
     * @param array $settings
     */
    public function __construct(?array $settings)
    {
        if ($settings === null) {
            return;
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
     * @deprecated Move this to parameters only.  Lots of work to do here...
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
     */
    public function setSettingByScope(string $scope, string $name, $value): self
    {
        $setting = $this->getSettingByScope($scope, $name, true);
        if (false === $setting)
            throw new SettingNotFoundException($scope, $name);


        $setting = $this->getRepository()->findOneBy(['scope' => $setting->getScope(), 'name' => $setting->getName()]);
        $this->setEntity($setting);

        if (is_array($value)) {
            $value = implode(',', $value);
        } else if ($value instanceof \DateTimeImmutable) {
            $value = $value->format('c');
        } else if ($value instanceof EntityInterface) {
            $value = $value->getId();
        }


        $setting->setValue($value);
        $this->saveEntity();
        $this->addSetting($setting);
        $this->writeSettingInSession($setting);
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSettings(): ArrayCollection
    {
        if (null === $this->settings) {
            $this->settings = new ArrayCollection();
        }
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
     * getSetting
     * @param $scope
     * @param $name
     * @return mixed
     */
    public function getSetting($scope, $name)
    {
        if (!$this->getSettings()->containsKey($scope)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not exist in the Settings', $scope));
        }

        if (!$this->settings->get($scope)->containskey($name)) {
            throw new \InvalidArgumentException(sprintf('The scope "%s" does not does not contain a setting "%s"', $scope, $name));
        }
        $setting = $this->settings->get($scope)->get($name);
        $value = null;
        switch ($setting['type']) {
            case 'string':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'];
                } else {
                    throw new \InvalidArgumentException(sprintf('The setting "%s", "%s" is not a valid string.', $scope,$name));
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Please write code to handle %s', $setting['type']));
        }
        return $value;
    }

    /**
     * hasSettingByScope
     * @param string $scope
     * @param string $name
     * @param bool $testForEmpty
     * @return bool
     * @throws \Exception
     */
    public function hasSetting(string $scope, string $name, bool $testForEmpty = true): bool
    {
        $setting = $this->getSettingByScope($scope, $name, true);
        if (!$setting instanceof Setting)
            return false;

        if (! $testForEmpty)
            return true;

        if (null === $setting->getValue() || '' === $setting->getValue())
            return false;

        return true;
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
     * saveSettings
     *
     * Recursive
     * @param FormInterface $form
     * @param array $content
     */
    private function saveSettings(FormInterface $form, array $content)
    {
        foreach($form->all() as $child)
        {
            if (get_class($child->getConfig()->getType()->getInnerType()) === SettingsType::class)
            {
                foreach($child->getConfig()->getOption('settings') as $setting) {
                    $name = str_replace(' ', '_', $setting['scope'].'__'.$setting['name']);
                    $settingForm = $child->get($name);
                    $data = $settingForm->getData();

                    if ($data instanceof EntityInterface) {
                        $data = $data->getId();
                    }

                    if ($data instanceof File) {
                        $data = str_replace(realpath(__DIR__ . '/../../public'), '', $data->getRealPath());
                    }

                    if ($data instanceof \DateTimeImmutable || $data instanceof \DateTime) {
                        $data = $data->format('c');
                    }

                    if ($data instanceof Collection) {
                        $data = json_encode($data->toArray());
                    }

                    if (is_object($data)) {
                        dump(get_class($data), $data);
                        throw new \InvalidArgumentException('Work out how to handle an object!');
                    }

                    if (is_array($data)) {
                        $data = implode(',', $data);
                    }

                    $this->setSettingByScope($setting['scope'], $setting['name'], $data);
                }
            }
            $this->saveSettings($child, $content);
        }
    }

    /**
     * getSession
     * @return SessionInterface
     */
    public function getSession(): ?SessionInterface
    {
        if ($this->getStack()->getCurrentRequest() && $this->getStack()->getCurrentRequest()->getSession())
            return $this->getStack()->getCurrentRequest()->getSession();
        return null;
    }

    /**
     * @var array
     */
    private $sessionSettings = [
    ];

    /**
     * writeSettingInSession
     * @param Setting $setting
     */
    private function writeSettingInSession(Setting $setting): void
    {
        if (null === $this->getSession())
            return;

        $this->getSession()->set('settings', $this->getSettings());
        if (isset($this->sessionSettings[$setting->getScope()][$setting->getName()]))
            $this->getSession()->set($this->sessionSettings[$setting->getScope()][$setting->getName()], $setting->getValue());

        if ($setting->getScope() === 'System')
            $this->getSession()->set($setting->getName(), $setting->getValue());

    }

    /**
     * getSettingByName
     * @param string $name
     * @return Setting|null
     * @throws \Exception
     */
    public function getSettingByName(string $name): ?Setting
    {
        $result = $this->getRepository()->findBy(['name' => $name]);

        if (count($result) === 1)
        {
            $this->addSetting($result[0]);

            return $result[0];
        }
        return null;
    }

    /**
     * getSettingByNameAsString
     * @param string $name
     * @return string|null
     * @throws \Exception
     */
    public function getSettingByNameAsString(string $name): ?string
    {
        $result = $this->getSettingByName($name);
        return $result !== null ? $result->getValue() : null;
    }

    /**
     * getSettingByScopeAsObject
     *
     * Assumes that the Setting value is the identifier of the class provided.
     * @param string $scope
     * @param string $name
     * @param string $class
     * @return EntityInterface|null
     * @throws \Exception
     */
    public function getSettingByScopeAsObject(string $scope, string $name, string $class): ?EntityInterface
    {
        $result = $this->getSettingByScopeAsInteger($scope, $name);
        if ($result === null)
            return null;
        return $this->getRepository($class)->find($result);
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
}