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

namespace App\Modules\System\Provider;

use App\Provider\EntityProviderInterface;
use Doctrine\DBAL\Exception\DriverException;
use App\Modules\System\Entity\I18n;
use Doctrine\Common\Collections\Collection;
use App\Modules\People\Entity\Person;
use App\Modules\System\Entity\Setting;
use App\Modules\System\Exception\SettingNotFoundException;
use App\Modules\System\Form\SettingsType;
use App\Manager\EntityInterface;
use App\Manager\Traits\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Gibbon\Contracts\Services\Session;
use Gibbon\Services\Format;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SettingProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = Setting::class;

    /**
     * @var ArrayCollection
     */
    private $settings;

    /**
     * getSettingByScope
     * @param string $scope
     * @param string $name
     * @param bool $returnRow
     * @return mixed
     * @throws \Exception
     */
    public function getSettingByScope(string $scope, string $name, $returnRow = false)
    {
        try {
            $setting = $this->getSetting($scope, $name) ?: $this->findOneBy(['scope' => $scope, 'name' => $name]);
        } catch (DriverException $e) {
            $setting = null;
        }

        if (null === $setting) {
            return false;
        }

        $this->addSetting($setting);

        if ($returnRow) {
            return $setting;
        }

        return $setting->getValue();
    }


    /**
     * getSettingsByScope
     * @param string $scope
     * @return SettingProvider
     * @throws \Exception
     */
    public function getSettingsByScope(string $scope): SettingProvider
    {
        $settings = $this->getRepository()->findByScope($scope);

        foreach($settings as $setting)
            $this->addSetting($setting);

        return $this;
    }

    /**
     * getSystemSettings
     * @throws \Exception
     */
    public function getSystemSettings(Session $session)
    {
        $session->set('systemSettingsSet', false);
        //System settings from gibbonSetting
        $result = $this->findBy(['scope' => 'System']);

        foreach($result as $setting)
        {
            $session->set($setting->getName(), $setting->getValue());
        }

        //Get names and emails for administrator, dba, admissions
        //System Administrator
        $result = $this->getRepository(Person::class)->findOneBy(['id' => $session->get('organisationAdministrator')]);
        $session->set('organisationAdministratorName', Format::name('', $result->getPreferredName(), $result->getSurname(), 'Staff', false, true));
        $session->set('organisationAdministratorEmail', $result->getEmail());

        //DBA
        $result = $this->getRepository(Person::class)->findOneBy(['id' => $session->get('organisationDBA')]);
        $session->set('organisationDBAName', Format::name('', $result->getPreferredName(), $result->getSurname(), 'Staff', false, true));
        $session->set('organisationDBAEmail', $result->getEmail());

        //Admissions
        $result = $this->getRepository(Person::class)->findOneBy(['id' => $session->get('organisationAdmissions')]);
        $session->set('organisationAdmissionsName', Format::name('', $result->getPreferredName(), $result->getSurname(), 'Staff', false, true));
        $session->set('organisationAdmissionsEmail', $result->getEmail());

        //HR Administraotr
        $result = $this->getRepository(Person::class)->findOneBy(['id' => $session->get('organisationHR')]);
        $session->set('organisationHRName', Format::name('', $result->getPreferredName(), $result->getSurname(), 'Staff', false, true));
        $session->set('organisationHREmail', $result->getEmail());

        //Language settings from gibboni18n
        $result = $this->getProviderFactory()->getProvider(I18n::class)->setLanguageSession($session);

        $session->set('systemSettingsSet',true);
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

        if (is_array($value))
            $value = implode(',',$value);
        if ($value instanceof \DateTimeImmutable)
            $value = $value->format('c');

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
            if (null === $this->getSession())
                $this->settings = new ArrayCollection();
            else {
                if ($this->getSession()->has('settings'))
                    $this->settings = $this->getSession()->get('settings');
                if (!$this->settings instanceof ArrayCollection)
                    $this->settings = new ArrayCollection();
            }
        }
        return $this->settings;
    }

    /**
     * Settings.
     *
     * @param ArrayCollection $settings
     * @return SettingProvider
     */
    public function setSettings(ArrayCollection $settings): SettingProvider
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * addSetting
     * @param Setting $setting
     * @return SettingProvider
     */
    private function addSetting(Setting $setting): SettingProvider
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
     * @return |null
     */
    private function getSetting($scope, $name)
    {
        if (!$this->getSettings()->containsKey($scope))
            $this->settings->set($scope, new ArrayCollection());

        return $this->settings->get($scope)->containskey($name) ? $this->settings->get($scope)->get($name) : null;
    }

    /**
     * hasSettingByScope
     * @param string $scope
     * @param string $name
     * @param bool $testForEmpty
     * @return bool
     * @throws \Exception
     */
    public function hasSettingByScope(string $scope, string $name, bool $testForEmpty = true): bool
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
     * @param array $errors
     * @return SettingProvider
     */
    public function addError(array $error): SettingProvider
    {
        $this->getErrors();
        $this->errors[] = $error;
        return $this;
    }

    /**
     * handleSettingsForm
     * @param FormInterface $form
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return array
     */
    public function handleSettingsForm(FormInterface $form, Request $request, TranslatorInterface $translator): array
    {
        $content = json_decode($request->getContent(), true);

        $form->submit($content);

        if ($form->isValid()) {
            $this->saveSettings($form, $content, $translator);
            if (count($this->getErrors()) === 0) {
                $data['status'] = 'success';
                return $this->addError(['class' => 'success', 'message' => $translator->trans('return.success.0', [], 'messages')])->getErrors();
            }
            else
                return $this->getErrors();
        }
        foreach($form->getErrors(true) as $error)
        {
            $this->addError(['class' => 'error', 'message' => $error->getOrigin()->getName() . ': ' . $error->getMessage()]);
        }

        return $this->addError(['class' => 'error', 'message' => $translator->trans('return.error.1', [], 'messages')])->getErrors();
    }

    /**
     * saveSettings
     *
     * Recursive
     * @param FormInterface $form
     */
    private function saveSettings(FormInterface $form, array $content, TranslatorInterface $translator)
    {
        foreach($form->all() as $child)
        {
            if (get_class($child->getConfig()->getType()->getInnerType()) === SettingsType::class)
            {
                foreach($child->getConfig()->getOption('settings') as $setting) {
                    $name = str_replace(' ', '_', $setting['scope'].'__'.$setting['name']);
                    $settingForm = $child->get($name);
                    $data = $settingForm->getData();

                    if ($data instanceof EntityInterface)
                        $data = $data->getId();

                    if ($data instanceof File)
                        $data = str_replace(realpath(__DIR__ . '/../../public'), '', $data->getRealPath());

                    if ($data instanceof \DateTimeImmutable || $data instanceof \DateTime)
                        $data = $data->format('c');

                    if ($data instanceof Collection)
                    {
                        $data = json_encode($data->toArray());
                    }

                    if (is_object($data))
                        dump(get_class($data), $data);

                    if (is_array($data))
                        $data = implode(',',$data);

                    $this->setSettingByScope($setting['scope'], $setting['name'], $data);
                }
            }
            $this->saveSettings($child, $content, $translator);
        }
    }

    /**
     * getSession
     * @return SessionInterface
     */
    public function getSession(): ?SessionInterface
    {
        if ($this->stack->getCurrentRequest() && $this->stack->getCurrentRequest()->getSession())
            return $this->stack->getCurrentRequest()->getSession();
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
     * @param int|null $default
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