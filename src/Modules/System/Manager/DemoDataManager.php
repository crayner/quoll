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
 * Date: 28/04/2020
 * Time: 08:47
 */
namespace App\Modules\System\Manager;

use App\Manager\AbstractEntity;
use App\Manager\EntityInterface;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberAdult;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Entity\Facility;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Staff\Entity\Staff;
use App\Modules\School\Entity\House;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DemoDataManager
 * @package App\Modules\System\Manager
 * @author Craig Rayner <craig@craigrayner.com>
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
        'department' => Department::class,
        'person' => Person::class,
        'person2' => Person::class,
        'department_staff' => DepartmentStaff::class,
        'family' => Family::class,
        'family_adult' => FamilyMemberAdult::class,
        'family_child' => FamilyMemberStudent::class,
        'facility' => Facility::class,
        'roll_group' => RollGroup::class,
        'student_enrolment' => StudentEnrolment::class,
        'indescriptor' => INDescriptor::class,
        'academic_year' => AcademicYear::class,
        'academic_year_term' => AcademicYearTerm::class,
        'academic_year_special_day' => AcademicYearSpecialDay::class,
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
     * @param string $table
     */
    public function execute(string $table)
    {
        foreach($this->entities as $name => $entityName)
        {
            if ($name === $table || $table === '') {
                if ($this->isEntityEmpty($name, $entityName)) {
                    $this->load($name, $entityName);
                } else {
                    $this->logger->warning(sprintf('%s already has data. No changes made for %s file.', $entityName, $name));
                }
            }
        }
    }

    /**
     * isEntityEmpty
     * @param string $name
     * @param string $entityName
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
            $entity = $this->renderDefaultValues($entity, $rules['defaults']);
            $entity = $this->renderConstantValues($entity, $rules['constants']);
            foreach($w as $propertyName => $value) {
                $method = 'set' . ucfirst($propertyName);

                if (method_exists($entity, $method)) {
                    if (key_exists($propertyName, $rules['associated'])) {
                        $value = $this->getAssociatedValue($value, $name, $propertyName);
                    }
                    if (key_exists($propertyName, $rules['properties'])) {
                        $value = $this->transformPropertyValue($rules['properties'][$propertyName], $value);
                    }
                    try {
                        $entity->$method($value);
                    } catch (\TypeError | \Exception $e) {
                        $this->getLogger()->warning($e->getMessage(), is_array($value) ? $value : ['value' => $value]);
                    }
                } else
                    $this->getLogger()->warning(sprintf('A setter was not found for %s in %s', $propertyName, $entityName));
            }

            $validatorList = $validator->validate($entity);
            if ($validatorList->count() === 0) {
                $data = ProviderFactory::create($entityName)->persistFlush($entity, [], false);
                if ($data['status'] !== 'success')
                    $this->getLogger->error('Something when wrong with persist:' . $data['errors'][0]['message'], [$entity]);
                $valid++;
            } else {
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', $entityName), [$w, $entity, $validatorList->__toString()]);
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
     * @return EntityInterface|EntityInterface[]|ArrayCollection|null
     */
    private function getAssociatedValue($value, string $name, string $propertyName)
    {
        $rules = $this->getEntityRules($name);

        if (!key_exists($propertyName, $rules['associated']))
            return $value;

        if (!is_array($value)) {
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
                    'useCollection' => false,
                    'method' => null,
                ]
            );

            $associateRules = $resolver->resolve($rules['associated'][$propertyName]);

            $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy([$associateRules['findBy'] => $value]);

            if ($this->associatedEntities[$propertyName][$key] !== null
                && $associateRules['method'] !== null
                && method_exists($this->associatedEntities[$propertyName][$key], $associateRules['method'])) {
                $method = $associateRules['method'];
                $this->associatedEntities[$propertyName][$key] = $this->associatedEntities[$propertyName][$key]->$method();
            }

            if (null === $this->associatedEntities[$propertyName][$key]) {
                $this->getLogger()->notice(sprintf('The entity %s does not have a row defined by %s => %s', $associateRules['entityName'], $associateRules['findBy'], (string)$value));
            }

            return $this->associatedEntities[$propertyName][$key];

        } else {
            $resolver = new OptionsResolver();
            $resolver->setRequired(
                [
                    'entityName',
                ]
            );
            $resolver->setDefaults(
                [
                    'findBy' => [],
                    'useCollection' => false,
                    'create' => false,
                    'associated' => [],
                    'getMethod' => null,
                ]
            );

            $associateRules = $resolver->resolve($rules['associated'][$propertyName]);

            if ($associateRules['create']) {
                return $this->createSubEntity($associateRules, $value);
            }

            if ($associateRules['useCollection']) {
                $result = new ArrayCollection();
                foreach($value as $q=>$w) {
                    if (!is_array($associateRules['findBy'])) {
                        $associateRules['findBy'] = [$associateRules['findBy']];
                    }
                    if (!is_array($w)) {
                        $w = [$w];
                    }

                    $criteria = [];
                    foreach($associateRules['findBy'] as $a=>$b) {
                        $criteria[$b] = $value[$a];
                    }

                    $item = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy($criteria);
                    if (null !== $item) {
                        $result->add($item);
                    } else {
                        $this->getLogger()->notice(sprintf('The entity %s does not have a row defined by %s => %s', $associateRules['entityName'], (string)$associateRules['findBy'], (string)$value));
                    }
                }
                return $result;
            }
            $key = '';
            foreach($value as $q=>$w) {
                $key .= $q.'.';
            }
            $key = trim($key, '.');

            if (key_exists($propertyName, $this->associatedEntities))
                if (key_exists($key, $this->associatedEntities[$propertyName]))
                    return $this->associatedEntities[$propertyName][$key];

            if (is_string($rules['associated'][$propertyName]))
                $rules['associated'][$propertyName] = ['entityName' => $rules['associated'][$propertyName]];

            if ($associateRules['method']) {
                return $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy($value)->$associateRules['method']();
            }
            return $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy($value);
        }
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
                dump($value);
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
    private function renderDefaultValues($entity, array $defaults): EntityInterface
    {
        if (!class_implements($entity, EntityInterface::class)) {
            throw new \InvalidArgumentException(sprintf('The class %s does not implement %s. Ensure that the entity file extends %s or implements %s.', get_class($entity), EntityInterface::class, AbstractEntity::class, EntityInterface::class));
        }
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

    /**
     * createSubEntity
     * @param array $associatedRules
     * @param array $data
     * @return mixed
     */
    private function createSubEntity(array $associatedRules, array $data)
    {
        $entity = new $associatedRules['entityName']();
        foreach($data as $name=>$value) {
            $method = 'set' . ucfirst($name);

            if (is_array($value) && isset($value['entityName'])) {
                $value = $this->getAssociatedEntity($name, $value);
            }

            if (method_exists($entity, $method)) {
                try {
                    $entity->$method($value);
                } catch (\InvalidArgumentException $e) {
                    dump($entity,$name,$value);
                    throw $e;
                }
            } else {
                $this->getLogger()->warning(sprintf('A setter was not found for %s in %s', $name, get_class($entity)));
            }
        }

        return $entity;
    }

    /**
     * getAssociatedEntity
     * @param string $propertyName
     * @param array $value
     * @return EntityInterface
     * 10/07/2020 13:56
     */
    private function getAssociatedEntity(string $propertyName, array $value): EntityInterface
    {
        $key = strval($value['value']);

        if (key_exists($propertyName, $this->associatedEntities))
            if (key_exists($key, $this->associatedEntities[$propertyName]))
                return $this->associatedEntities[$propertyName][$key];
        $resolver = new OptionsResolver();
        $resolver->setRequired(
            [
                'entityName',
                'value',
            ]
        );
        $resolver->setDefaults(
            [
                'findBy' => 'id',
                'useCollection' => false,

            ]
        );

        $value = $resolver->resolve($value);

        $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($value['entityName'])->findOneBy([$value['findBy'] => $value['value']]);

        if (null === $this->associatedEntities[$propertyName][$key]) {
            $this->getLogger()->notice(sprintf('The entity %s does not have a row defined by %s => %s', $value['entityName'], $value['findBy'], (string)$value));
        }

        return $this->associatedEntities[$propertyName][$key];
    }
}
