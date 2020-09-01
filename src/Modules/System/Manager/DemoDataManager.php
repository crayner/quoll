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
use App\Manager\StatusManager;
use App\Modules\Curriculum\Entity\Course;
use App\Modules\Department\Entity\Department;
use App\Modules\Department\Entity\DepartmentStaff;
use App\Modules\Enrolment\Entity\CourseClass;
use App\Modules\Enrolment\Entity\StudentEnrolment;
use App\Modules\IndividualNeed\Entity\INDescriptor;
use App\Modules\People\Entity\Family;
use App\Modules\People\Entity\FamilyMemberCareGiver;
use App\Modules\People\Entity\FamilyMemberStudent;
use App\Modules\People\Entity\Person;
use App\Modules\School\Entity\AcademicYear;
use App\Modules\School\Entity\AcademicYearSpecialDay;
use App\Modules\School\Entity\AcademicYearTerm;
use App\Modules\School\Entity\Facility;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\School\Entity\House;
use App\Modules\Timetable\Entity\Timetable;
use App\Modules\Timetable\Entity\TimetableDate;
use App\Modules\Timetable\Entity\TimetableDay;
use App\Modules\Timetable\Entity\TimetablePeriod;
use App\Modules\Timetable\Util\TimetableDemoData;
use App\Provider\ProviderFactory;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;
use TypeError;

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
        'family_student' => FamilyMemberStudent::class,
        'family_care_giver' => FamilyMemberCareGiver::class,
        'facility' => Facility::class,
        'roll_group' => RollGroup::class,
        'student_enrolment' => StudentEnrolment::class,
        'indescriptor' => INDescriptor::class,
        'academic_year' => AcademicYear::class,
        'academic_year_term' => AcademicYearTerm::class,
        'academic_year_special_day' => AcademicYearSpecialDay::class,
        'timetable' => Timetable::class,
        'timetable_day' => TimetableDay::class,
        'timetable_period' => TimetablePeriod::class,
        'timetable_date' => TimetableDate::class,
        'course' => Course::class,
        'course_class' => CourseClass::class,
    ];

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var StatusManager
     */
    private StatusManager $messages;

    /**
     * DemoDataManager constructor.
     * @param LoggerInterface $logger
     * @param ValidatorInterface $validator
     */
    public function __construct(LoggerInterface $logger, ValidatorInterface $validator, TimetableDemoData $tdd, StatusManager $messages)
    {
        $this->logger = $logger;
        $this->dataPath = realpath($this->dataPath) . DIRECTORY_SEPARATOR;
        $this->validator = $validator;
        $this->messages = $messages;
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
        $this->rules = [];
        $rules = $this->getEntityRules($name);
        $this->getLogger()->notice(sprintf('Loading %s file into %s', $name, $entityName));
        ini_set('max_execution_time', 60);

        if (key_exists('call', $content)) {
            $implements = class_implements($content['call']['class']);
            if (in_array(DemoDataInterface::class, $implements)) {
                $method = $content['call']['method'];
                $class = $content['call']['class'];
                $class::$method($this->getLogger());
            } else {
                $this->getLogger()->error(sprintf('You have not provided a correctly formatted call, or the method is missing or the class does not implement %s in %s', DemoDataInterface::class,$name));
                throw new InvalidArgumentException(sprintf('You have not provided a correctly formatted call, or the method is missing or the class does not implement %s in %s', DemoDataInterface::class,$name));
            }
            return ;
        }

        $valid = 0;
        $invalid = 0;
        foreach($content as $q=>$w) {
            $entity = new $entityName();
            $entity = $this->renderDefaultValues($entity, $rules['defaults']);
            $entity = $this->renderConstantValues($entity, $rules['constants']);
            foreach($w as $propertyName => $value) {
                $method = 'set' . ucfirst($propertyName);

                if (method_exists($entity, $method)) {
                    if (key_exists($propertyName, $rules['associated'])) {
                        $value = $this->getAssociatedValue($value, $name, $propertyName);
                    } else if (key_exists($propertyName, $rules['properties'])) {
                        $value = $this->transformPropertyValue($rules['properties'][$propertyName], $value);
                    } else if (is_array($value) && key_exists('entityName', $value) && key_exists('findBy', $value) && key_exists('value', $value)) {
                        $value = $this->getAssociatedEntity($propertyName, $value, $rules);
                    } else if (is_array($value) && key_exists('datetimeimmutable', $value)) {
                        $value = new DateTimeImmutable($value['datetimeimmutable']);
                    }

                    try {
                        $entity->$method($value);
                    } catch (TypeError | Exception $e) {
                        $this->getLogger()->warning($e->getMessage(), is_array($value) ? $value : ['value' => $value]);
                    }
                } else
                    $this->getLogger()->warning(sprintf('A setter was not found for %s in %s', $propertyName, $entityName));
            }

            $validatorList = $validator->validate($entity);
            if ($validatorList->count() === 0) {
                ProviderFactory::create($entityName)->persistFlush($entity,false);
                if (!$this->getMessages()->isStatusSuccess())
                    $this->getLogger()->error('Something when wrong with persist:' . $this->getMessages()->getLastMessageTranslated());
                $valid++;
            } else {
                $this->getLogger()->warning(sprintf('An entity failed validation for %s', $entityName), [$w, $entity, $validatorList->__toString()]);
                $invalid++;
            }

            if (($valid + $invalid) % 50 === 0 && ($valid + $invalid) !== 0) {
                $this->flush(sprintf('50 (to %s) records pushed to the database for %s from %s', $valid, $entityName, strval(count($content))));
                ini_set('max_execution_time', 10);
            }
        }
        if (($valid + $invalid) > 0) $this->getLogger()->notice('Count = ' . $valid . ' created in ' . get_class($entity));
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

            if (key_exists($propertyName, $this->associatedEntities)) {
                if (key_exists($key, $this->associatedEntities[$propertyName])) {
                    return $this->associatedEntities[$propertyName][$key];
                }
            }
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
                    'preLoad' => '',
                ]
            );
            $resolver->setAllowedTypes('preLoad', ['string']);

            $associateRules = $resolver->resolve($rules['associated'][$propertyName]);
            if ($associateRules['preLoad'] !== '' && !key_exists($propertyName, $this->associatedEntities)) {
                $method = $associateRules['preLoad'];
                $this->associatedEntities[$propertyName] = ProviderFactory::getRepository($associateRules['entityName'])->$method();
            }

            if ($associateRules['preLoad'] === '' && (!key_exists($propertyName, $this->associatedEntities) || !key_exists($key, $this->associatedEntities[$propertyName]))) {
                $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy([$associateRules['findBy'] => $value]);
            }


            if (key_exists($key, $this->associatedEntities[$propertyName])
                && $associateRules['method'] !== null
                && method_exists($this->associatedEntities[$propertyName][$key], $associateRules['method'])) {
                $method = $associateRules['method'];
                $this->associatedEntities[$propertyName][$key] = $this->associatedEntities[$propertyName][$key]->$method();
            }

            if (!key_exists($key, $this->associatedEntities[$propertyName])) {
                $this->getLogger()->notice(sprintf('The entity %s does not have a row defined by %s => %s', $associateRules['entityName'], $associateRules['findBy'], (string)$value));
                return null;
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
                return $this->createSubEntity($associateRules, $value, $this->getEntityRules($name));
            }

            if ($associateRules['useCollection']) {
                $result = new ArrayCollection();
                foreach($value as $q=>$w) {
                    if (!is_array($associateRules['findBy'])) {
                        $associateRules['findBy'] = [$associateRules['findBy']];
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

            if (key_exists('method', $associateRules)) {
                return $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy($value)->$associateRules['method']();
            }



            dump($associateRules, $value);

            return $this->associatedEntities[$propertyName][$key] = ProviderFactory::getRepository($associateRules['entityName'])->findOneBy($value);
        }
    }

    /**
     * transformPropertyValue
     * @param string $type
     * @param $value
     * @return DateTimeImmutable|null
     */
    private function transformPropertyValue(string $type, $value)
    {
        switch ($type) {
            case 'DateTimeImmutable':
                if (empty($value))
                    return null;
                try {
                    return new DateTimeImmutable($value);
                } catch (Exception $e) {
                    return null;
                }
            case 'CollectionEntity':
                if (empty($value)) return null;
                $entities = new ArrayCollection();
                foreach ($value as $item) {
                    if (is_array($item) && key_exists('entityName', $item) && key_exists('findBy', $item) && key_exists('value', $item)) {
                        $criteria = [];
                        if (is_array($item['findBy'])) {
                            foreach ($item['findBy'] as $q=>$w) {
                                $criteria[$w] = $item['value'][$q];
                            }
                        } else {
                            $criteria[$item['findBy']] = $item['value'];
                        }
                        $entities->add(ProviderFactory::getRepository($item['entityName'])->findOneBy($criteria));
                    } else {
                        $this->getLogger()->warning(sprintf('Not able to transform the value into a %s.  The value must be an array of objects, each object with keys: entityName, findBy, value to uniquely identify the entity.', $type));
                    }
                }
                return $entities;
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
            throw new InvalidArgumentException(sprintf('The class %s does not implement %s. Ensure that the entity file extends %s or implements %s.', get_class($entity), EntityInterface::class, AbstractEntity::class, EntityInterface::class));
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
     *
     * 29/08/2020 09:26
     * @param array $associatedRules
     * @param array $data
     * @param array $entityRules
     * @return mixed
     * @throws Exception
     */
    private function createSubEntity(array $associatedRules, array $data, array $entityRules)
    {
        $entity = new $associatedRules['entityName']();

        foreach($data as $name=>$value) {
            $method = 'set' . ucfirst($name);

            if (is_array($value) && key_exists('entityName', $value)) {
                $value = $this->getAssociatedEntity($name, $value, $entityRules);
            }

            if (is_array($value) && key_exists('datetimeimmutable', $value)) $value = new DateTimeImmutable($value['datetimeimmutable']);

            if (method_exists($entity, $method)) {
                try {
                    $entity->$method($value);
                } catch (InvalidArgumentException $e) {
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
     *
     * 28/08/2020 08:51
     * @param string $propertyName
     * @param array $value
     * @param string $name
     * @return EntityInterface
     */
    private function getAssociatedEntity(string $propertyName, array $value, array $rules): EntityInterface
    {
        $key = strval($value['value']);

        $associateRules = key_exists($propertyName, $rules['associated']) ? $rules['associated'][$propertyName] : [];
        if (key_exists('preLoad', $associateRules) && !key_exists($propertyName, $this->associatedEntities)) {
            $method = $associateRules['preLoad'];
            $this->associatedEntities[$propertyName] = ProviderFactory::getRepository($associateRules['entityName'])->$method();
        }

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

    /**
     * getEntities
     *
     * 30/08/2020 07:11
     * @return string[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @return StatusManager
     */
    public function getMessages(): StatusManager
    {
        return $this->messages;
    }
}
