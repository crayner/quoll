<?php
/**
 * Created by PhpStorm.
 *
 * quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 28/04/2020
 * Time: 08:47
 */

namespace App\Modules\System\Manager;

use App\Manager\EntityInterface;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyAdult;
use App\Modules\People\Entity\FamilyChild;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\House;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\Driver\PDOException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DemoDataManager
 * @package App\Modules\System\Manager
 */
class DemoDataManager
{
    /**
     * @var string
     */
    private $dataPath = __DIR__ . '/../../../../Demo';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $rules = [];

    /**
     * @var array
     */
    private $associatedEntities;

    /**
     * @var string[]
     */
    private $entities = [
        'house' => House::class,
        'person' => Person::class,
        'person2' => Person::class,
        'family' => Family::class,
        'family_adult' => FamilyAdult::class,
        'family_child' => FamilyChild::class,
    ];

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * DemoDataManager constructor.
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     */
    public function __construct(LoggerInterface $logger, ValidatorInterface $validator)
    {
        $this->logger = $logger;
        $this->dataPath = realpath($this->dataPath) . DIRECTORY_SEPARATOR;
        $this->validator = $validator;
    }

    /**
     * @return string
     */
    public function getDataPath(): string
    {
        return $this->dataPath;
    }

    /**
     * execute
     */
    public function execute()
    {
        foreach($this->entities as $name => $entityName)
        {
            if ($this->isEntityEmpty($name, $entityName)) {
                $this->load($name, $entityName);
            } else {
                $this->logger->warning(sprintf('%s already has data. No changes made for %s file.', $entityName, $name));
            }
        }
    }

    /**
     * isEntityEmpty
     * @param $entityName
     * @return bool
     */
    private function isEntityEmpty(string $name, string $entityName)
    {
        $rules = $this->getEntityRules($name);
        return intval(ProviderFactory::create($entityName)->count()) <= $rules['empty_count'];
    }

    /**
     * load
     * @param $name
     * @param $entityName
     */
    private function load($name, $entityName)
    {
        $file = new File($this->getDataPath() . $name . '.yaml');
        $content = Yaml::parse(file_get_contents($file->getRealPath()));
        $validator = $this->validator;
        $this->associatedEntities = [];
        $rules = $this->getEntityRules($name);
        $this->getLogger()->notice(sprintf('Loading %s file into %s', $name, $entityName));
        ini_set('max_execution_time', 60);

        $valid = 0;
        foreach($content as $q=>$w) {
            $entity = new $entityName();
            foreach($w as $propertyName => $value) {
                $method = 'set' . ucfirst($propertyName);

                if (method_exists($entity, $method)) {
                    if (key_exists($propertyName, $rules['associated']))
                        $value = $this->getAssociatedValue($value, $name, $propertyName);
                    if (key_exists($propertyName, $rules['properties'])) {
                        $value = $this->transformPropertyValue($rules['properties'][$propertyName], $value);
                    }
                    try {
                        $entity->$method($value);
                    } catch (\TypeError | \Exception $e) {
                        $this->getLogger()->warning($e->getMessage());
                    }
                } else
                    $this->getLogger()->warning(sprintf('A setter was not found for %s in %s', $propertyName, $entityName));
            }

            $entity = $this->renderDefaultValues($entity, $rules['defaults']);
            $entity = $this->renderConstantValues($entity, $rules['constants']);
            $validatorList = $validator->validate($entity);
            if ($validatorList->count() === 0) {
                $data = ProviderFactory::create($entityName)->persistFlush($entity, [], false);
                if ($data['status'] !== 'success')
                    $this->getLogger('Something when wrong with persist', [$entity]);
                $valid++;
            } else {
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', $entityName), [$entity, $validatorList->__toString()]);
            }

            if ($valid % 50 === 0 && $valid !== 0) {
                $this->flush(sprintf('50 (to %s) records pushed to the database for %s from %s', $valid, $entityName, strval(count($content))));
                ini_set('max_execution_time', 60);
            }

            if ($valid > 1150)
                $this->getLogger()->debug('Count = ' . $valid . $entity->getFormalName());
        }
        $this->flush(sprintf('%s records added to %s from a total of %s', strval($valid), $entityName, strval(count($content))));
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * entityRules
     * @param string $name
     * @return array
     */
    private function getEntityRules(string $name)
    {
        if (key_exists($name, $this->rules))
            return $this->rules[$name];
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'empty_count' => 0,
                'associated' => [],
                'properties' => [],
                'defaults' => [],
                'constants' => [],
            ]
        );
        if (is_file($this->getDataPath() . $name . '.rules.yaml')) {
            $data = Yaml::parse(file_get_contents($this->getDataPath() . $name . '.rules.yaml'));
        } else {
            $data = [];
        }

        return $this->rules[$name] = $resolver->resolve($data);
    }

    /**
     * getAssociatedValue
     * @param $value
     * @param string $name
     * @param string $propertyName
     * @return EntityInterface|null
     */
    private function getAssociatedValue($value, string $name, string $propertyName): ?EntityInterface
    {
        $rules = $this->getEntityRules($name);

        if (!key_exists($propertyName, $rules['associated']))
            return $value;

        $key = strval($value);

        if (key_exists($propertyName, $this->associatedEntities))
            if (key_exists($key, $this->associatedEntities[$propertyName]))
                return $this->associatedEntities[$propertyName][$key];

        if (is_string($rules['associated'][$propertyName]))
            $rules['associated'][$propertyName] = ['entityName' => $rules['associated'][$propertyName]];

        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'entityName',
            ]
        );
        $resolver->setDefaults(
            [
                'findBy' => 'id',
            ]
        );

        $associateRules = $resolver->resolve($rules['associated'][$propertyName]);

        return $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy([$associateRules['findBy'] => $value]);
    }

    /**
     * transformPropertyValue
     * @param string $type
     * @param $value
     * @return \DateTimeImmutable|null
     */
    private function transformPropertyValue(string $type, $value)
    {
        switch ($type) {
            case 'DateTimeImmutable':
                if (empty($value))
                    return null;
                try {
                    return new \DateTimeImmutable($value);
                } catch (\Exception $e) {
                    return null;
                }
                break;
            default:
                $this->getLogger()->warning(sprintf('Not able to transform the value %s into a %s', strval($value), $type));
                return $value;
        }
    }

    /**
     * flush
     * @param $message
     */
    private function flush($message)
    {
        try {
            ProviderFactory::getEntityManager()->flush();
            $this->getLogger()->notice($message);
        } catch (\PDOException | PDOException $e) {
            $this->getLogger()->error($e->getMessage());
        }
    }

    /**
     * renderDefaultValues
     * @param EntityInterface $entity
     * @param array $defaults
     * @return EntityInterface
     */
    private function renderDefaultValues(EntityInterface $entity, array $defaults): EntityInterface
    {
        if ($defaults === [])
            return $entity;

        foreach($defaults as $name=>$valueKey) {
            $method = 'get' . ucfirst($name);
            if (!method_exists($entity, $method))
                $method = 'is' . ucfirst($name);
            if (method_exists($entity, $method) && in_array($entity->$method(), ['',null,[]])) {
                $w = 'get' . ucfirst($valueKey);
                if (!method_exists($entity, $w))
                    $w = 'is' . ucfirst($valueKey);
                $set = 'set' . ucfirst($name);
                if (method_exists($entity, $w) && method_exists($entity, $set)) {
                    $entity->$set($entity->$w());
                }
            }
        }
        return $entity;
    }

    /**
     * renderConstantsValues
     * @param EntityInterface $entity
     * @param array $constants
     * @return EntityInterface
     */
    private function renderConstantValues(EntityInterface $entity, array $constants): EntityInterface
    {
        if ($constants === [])
            return $entity;

        foreach($constants as $name=>$value) {
            $method = 'get' . ucfirst($name);
            if (!method_exists($entity, $method))
                $method = 'is' . ucfirst($name);
            if (method_exists($entity, $method) && in_array($entity->$method(), ['',null,[]])) {
                $set = 'set' . ucfirst($name);
                if (method_exists($entity, $set)) {
                    $entity->$set($value);
                }
            }
        }
        return $entity;
    }
}