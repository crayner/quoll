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

use App\Manager\EntityInterface;
use App\Manager\StatusManager;
use App\Modules\System\Exception\SettingInvalidException;
use App\Modules\System\Exception\SettingNotFoundException;
use App\Modules\System\Form\SettingsType;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class SettingProvider
 * @package App\Modules\System\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
class SettingManager
{
    /**
     * @var ArrayCollection|null
     */
    private ?ArrayCollection $settings;

    /**
     * @var bool
     */
    private bool $settingsChanged = false;

    /**
     * @var SettingManager
     */
    private static SettingManager $instance;

    /**
     * @var StatusManager
     */
    private StatusManager $messages;

    /**
     * @var
     */
    private LoggerInterface $logger;

    /**
     * SettingProvider constructor.
     * @param array|null $settings
     * @param StatusManager $messages
     * @param LoggerInterface $logger
     */
    public function __construct(?array $settings, StatusManager $messages, LoggerInterface $logger)
    {
        if ($settings === null) {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists(__DIR__ . '/../../../../config/packages/settings.yaml')) {
                $fileSystem->copy(__DIR__ . '/../../../../config/packages/settings.yaml.dist',__DIR__ . '/../../../../config/packages/settings.yaml');
                $settings = Yaml::parse(file_get_contents(__DIR__ . '/../../../../config/packages/settings.yaml'));
                $settings = $settings['parameters']['settings'];
            }
        }
        $this->settings = $this->convertRawSettings($settings ?? []);
        $messages->setLogger($logger);
        $this->messages = $messages;
        $this->logger = $logger;
        self::$instance = $this;
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
                $valueType = $this->validateSetting($valueType);
                $scopeItems->set($name, $valueType);
            }
            $result->set($scope, $scopeItems);
        }
        return $result;
    }

    /**
     * validateSetting
     *
     * 16/08/2020 11:09
     * @param $setting
     * @return array
     */
    private function validateSetting($setting): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'value',
                'type',
            ]
        );
        $resolver->setDefaults(
            [
                'class' => null,
                'method' => null,
            ]
        );
        $resolver->setAllowedValues('type', ['string','integer','entity','image','array','boolean','float','enum']);
        try {
            return $resolver->resolve($setting);
        } catch (UndefinedOptionsException $e) {
            dump($setting);
            throw $e;
        }
    }

    /**
     * getSettingByScope
     *
     * 16/08/2020 13:12
     * @param string $scope
     * @param string $name
     * @return array|bool|int|object|string|null
     * @throws Exception
     * @deprecated Move this to get.  Lots of work to do here...
     */
    public function getSettingByScope(string $scope, string $name)
    {
        return $this->get($scope, $name);
    }


    /**
     * getSettingsByScope
     *
     * 16/08/2020 13:12
     * @param string $scope
     * @return ArrayCollection|null
     */
    public function getSettingsByScope(string $scope): ?ArrayCollection
    {
        return $this->getSettings()->containsKey($scope) ? $this->getSettings()->get($scope) : null;
    }

    /**
     * getSystemSettings
     *
     * 16/08/2020 13:13
     */
    public function getSystemSettings()
    {
        //System settings
        $result = $this->getSettingsByScope('System');
    }

    /**
     * setSettingByScope
     *
     * 16/08/2020 13:14
     * @param string $scope
     * @param string $name
     * @param $value
     * @return $this
     * @throws Exception
     */
    public function setSettingByScope(string $scope, string $name, $value): self
    {
        return $this->set($scope,$name,$value);
    }

    /**
     * setSetting
     * @param string $scope
     * @param string $name
     * @param $value
     * @return $this
     * @throws Exception
     * 22/07/2020 11:14
     * @deprecated Use set(string $scope, string $name, $value)
     */
    public function setSetting(string $scope, string $name, $value): self
    {
        return $this->set($scope, $name, $value);
    }

    /**
     * set
     * @param string $scope
     * @param string $name
     * @param $value
     * @return $this
     * 22/07/2020 11:14
     */
    public function set(string $scope, string $name, $value): self
    {
        if (!$this->has($scope,$name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);

        switch ($setting['type']) {
            case 'entity':
                $value = $value instanceof EntityInterface ? $value->getId() : $value;
                if ($setting['value'] !== $value) {
                    $setting['value'] = $value;
                    $this->setSettingsChanged();
                }
                break;
            case 'image':
            case 'string':
                if (is_null($value) || is_string($value)) {
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                }
                break;
            case 'integer':
                if (is_null($value) || is_int($value)) {
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                }
                break;
            case 'array':
                if (is_null($value) || is_array($value)) {
                    $value = $value ?? [];
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                }
                break;
            case 'boolean':
                if ((is_string($value) && in_array($value, ['1', '', '0'])) || is_bool($value) || is_null($value)) {
                    $value = (bool)$value;
                    if ($setting['value'] !== $value) {
                        $setting['value'] = $value;
                        $this->setSettingsChanged();
                    }
                }
                break;
            default:
                $this->getLogger()->error(sprintf('How do I save a %s', $setting['type']));
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
     * has
     *
     * 30/08/2020 10:07
     * @param string $scope
     * @param string $name
     * @param bool $isEmpty
     * @return bool
     */
    public function has(string $scope, string $name, bool $isEmpty = false): bool
    {
        if ($isEmpty) {
            if ($this->getSettings()->containsKey($scope) && $this->getSettings()->get($scope)->containsKey($name)) {
                $w = $this->getSettings()->get($scope)->get($name);
                switch ($w['type']) {
                    case 'entity':
                    case 'string':
                        return !in_array($w['value'], [null, '']);
                        break;
                    default:
                        $this->getLogger()->error('Missing has() type work for '.$w['type']);
                }
            }
        } else {
            return $this->getSettings()->containsKey($scope) && $this->getSettings()->get($scope)->containsKey($name);
        }
        return false;
    }

    /**
     * get
     * @param string $scope
     * @param string $name
     * @param null $default
     * @return array|bool|int|object|string|null
     * 22/07/2020 11:14
     */
    public function get(string $scope, string $name, $default = null)
    {
        if (!$this->has($scope, $name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        $value = null;
        switch ($setting['type']) {
            case 'enum':
            case 'string':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?: $default;
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                break;
            case 'boolean':
                if (is_bool($setting['value'])) {
                    $value = $setting['value'] ?: $default;
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                break;
            case 'integer':
                if (is_null($setting['value']) || is_integer($setting['value'])) {
                    $value = $setting['value'] ?: $default;
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                break;
            case 'array':
                if (is_null($setting['value']) || is_array($setting['value'])) {
                    $value = $setting['value'] ?: ($default ?: []);
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                break;
            case 'image':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?: $default;
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                if ($value !== null && $value !== '') {
                    if (!is_file(__DIR__ . '/../../../../public/' . ltrim($setting['value'], '/'))) {
                        $value = $default;
                    }
                }
                break;
            case 'entity':
                if (is_null($setting['value']) || is_string($setting['value'])) {
                    $value = $setting['value'] ?: $default;
                } else {
                    throw new SettingInvalidException($scope,$name,$setting['type']);
                }
                if (is_string($value)) {
                    $value = ProviderFactory::getRepository($setting['class'])->find($value);
                }
                break;
            default:
                $this->getLogger()->error(sprintf('Please write code to handle setting type %s', $setting['type']));
        }
        return $value;
    }

    /**
     * getSettingType
     *
     * 18/08/2020 10:42
     * @param string $scope
     * @param string $name
     * @return mixed
     */
    public function getSettingType(string $scope, string $name)
    {
        if (!$this->has($scope, $name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        return $setting['type'];
    }

    /**
     * getSettingClass
     *
     * 18/08/2020 10:42
     * @param string $scope
     * @param string $name
     * @return string|null
     */
    public function getSettingClass(string $scope, string $name): ?string
    {
        if (!$this->has($scope, $name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        if ($setting['class'] === null && in_array($setting['type'], ['enum','entity'])) $this->getLogger()->notice(sprintf('The setting defined by %s:%s does not have a class value', $scope, $name));
        return $setting['class'];
    }

    /**
     * getSettingMethod
     *
     * 18/08/2020 10:42
     * @param string $scope
     * @param string $name
     * @return string|null
     */
    public function getSettingMethod(string $scope, string $name): ?string
    {
        if (!$this->has($scope, $name)) {
            throw new SettingNotFoundException($scope, $name);
        }

        $setting = $this->getSettings()->get($scope)->get($name);
        if ($setting['method'] === null && in_array($setting['type'], ['enum'])) $this->getLogger()->notice(sprintf('The setting defined by %s:%s does not have a method value', $scope, $name));
        return $setting['method'];
    }

    /**
     * getMessages
     *
     * 16/08/2020 13:11
     * @param bool $setLogger
     * @return StatusManager
     */
    public function getMessages(bool $setLogger = true): StatusManager
    {
        if ($setLogger) $this->messages->setLogger($this->getLogger());
        return $this->messages;
    }

    /**
     * handleSettingsForm
     *
     * 16/08/2020 13:18
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    public function handleSettingsForm(FormInterface $form, Request $request): bool
    {
        $content = json_decode($request->getContent(), true);

        $form->submit($content);

        if ($form->isValid()) {
            $this->saveSettings($form, $content);
            if ($this->getMessages(false)->count() === 0) {
                $this->getMessages()->success();
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->getMessages()->error($error->getOrigin()->getName() . ': ' . $error->getMessage());
            }

            $this->getMessages()->error(StatusManager::INVALID_INPUTS);
        }
        return $this->getMessages(false)->getStatus() === 'success';
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getMessages(false)->getStatus();
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
     *
     * 18/08/2020 10:31
     */
    public function writeSettings()
    {
        if ($this->isSettingsChanged() && $this->getSettings()->count() > 0) {
            $settings = [];
            foreach($this->getSettings() as $q=>$w) {
                foreach($w as $a=>$b) {
                    foreach ($b as $z=>$x) {
                        if (in_array($z, ['method', 'class']) && $x === null) continue;
                        $settings[$q][$a][$z] = $x;
                    }
                }
            }

            file_put_contents(__DIR__ . '/../../../../config/packages/settings.yaml', Yaml::dump(['parameters' => ['settings' => $settings]], 8));
        }
    }

    /**
     * saveSettings
     *
     * 16/08/2020 13:18
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
                        $data = $data->toArray();
                    }

                    if (is_object($data)) {
                        $this->getLogger()->error(sprintf('Work out how to handle an object! %s', get_class($data)));
                    }

                    $this->set($setting['scope'], $setting['name'], $data);
                }
            }
            $this->saveSettings($child, $content);
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

}
