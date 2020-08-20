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
 * Date: 17/05/2020
 * Time: 12:54
 */
namespace App\Provider;

use App\Manager\EntityInterface;
use App\Manager\StatusManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractProvider
 * @package App\Provider
 * @author Craig Rayner <craig@craigrayner.com>
 */
abstract class AbstractProvider implements EntityProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var StatusManager
     */
    private StatusManager $messageManager;

    /**
     * @var EntityRepository
     */
    static private $entityRepository;

    /**
     * @var EntityInterface
     */
    private $entity;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $env = 'prod';

    /**
     * AbstractProvider constructor.
     * @param ProviderFactory $providerFactory
     * @throws Exception
     */
    public function __construct(ProviderFactory $providerFactory)
    {
        $this->entityManager = $providerFactory::getEntityManager();
        $this->messageManager = $providerFactory::getMessageManager();
        self::$entityRepository = $this->getRepository();
        $this->authorizationChecker = $providerFactory::getAuthorizationChecker();
        $this->router = $providerFactory::getRouter();
        $this->stack = $providerFactory::getStack();
        $this->providerFactory = $providerFactory;
        $this->parameterBag = ProviderFactory::getParameterBag();
        if (method_exists($this, 'additionalConstruct'))
            $this->additionalConstruct();
        $this->logger = $providerFactory::getLogger();
        $this->env = $_ENV['APP_ENV'];
    }

    /**
     * getEntityManager
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * getMessageManager
     *
     * 16/08/2020 14:58
     * @return StatusManager
     */
    public function getMessageManager(): StatusManager
    {
        return $this->messageManager;
    }

    /**
     * find
     * @param $id
     * @return EntityInterface|null
     * @throws Exception
     */
    public function find($id): ?EntityInterface
    {
        $this->entity = null;
        if ($id === 'Add')
            $this->entity = new $this->entityName();
        else {
            if ($this->getRepository() !== null)
                $this->entity = $this->getRepository()->find($id);
        }
        return $this->entity;
    }

    /**
     * delete
     *
     * @param $id
     * @return object|string
     * @throws Exception
     */
    public function delete($id)
    {
        if ($id === 'ignore') return $this->getEntity();
        if ($id instanceof $this->entityName) {
            $this->setEntity($id);
            $entity = $id;
        } else
            $entity = $this->find($id);
        if (empty($entity)) {
            return null;
        }

        if (method_exists($this, 'canDelete')) {
            if ($this->canDelete($entity)) {
                $this->getEntityManager()->remove($entity);
                $this->getEntityManager()->flush();
                $this->getMessageManager()->success();
                $this->entity = null;
                return $entity;
            } else {
                $this->getMessageManager()->warning(StatusManager::LOCKED_RECORD);
                return $entity;
            }
        } elseif (method_exists($entity, 'canDelete')) {
            if ($entity->canDelete()) {
                $this->getEntityManager()->remove($entity);
                $this->getEntityManager()->flush();
                $this->getMessageManager()->success();
                $this->entity = null;
                return $entity;
            } else {
                $this->getMessageManager()->warning(StatusManager::LOCKED_RECORD);
                return $entity;
            }
        } else {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
            $this->getMessageManager()->success();
            $this->entity = null;
            return $entity;
        }
    }

    /**
     * getEntityName
     *
     * @return string
     * @throws Exception
     */
    public function getEntityName(): string
    {
        if (empty($this->entityName)) {
            $this->getLogger()->error('You must specify the entity class [$entityName] in ' . get_class($this));
            throw new Exception('You must specify the entity class [$entityName] in ' . get_class($this));
        }
        return $this->entityName;
    }

    /**
     * getEntity
     *
     * 16/08/2020 14:44
     * @param EntityInterface|null $entity
     * @return EntityInterface|null
     */
    public function getEntity(EntityInterface $entity = null): ?EntityInterface
    {
        if ($entity instanceof $this->entityName)
            $this->setEntity($entity);
        return $this->entity;
    }

    /**
     * @param EntityInterface|null $entity
     * @return AbstractProvider
     */
    public function setEntity(?EntityInterface $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * getTransDomain
     *
     * @return string
     */
    public function getTransDomain(): string
    {
        if (empty($this->transDomain))
            return 'messages';
        return $this->transDomain;
    }

    /**
     * saveEntity
     * @param ValidatorInterface|null $validator
     * @param bool $flush
     * @return $this
     */
    public function saveEntity(?ValidatorInterface $validator = null, bool $flush = true)
    {
        if ($validator && ($list = $validator->validate($this->getEntity()))->count() > 0) {
            foreach ($list as $error)
                $this->getMessageManager()->error($error->getMessage(), [], false);
            return $this;
        }
        try {
            $this->getEntityManager()->persist($this->getEntity());
            if ($flush)
                $this->getEntityManager()->flush();
        } catch (Exception $e) {
            $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
        }
        return $this;
    }

    /**
     * getRepository
     *
     * @param string $className
     * @return ObjectRepository|null
     */
    public function getRepository(?string $className = ''): ?ObjectRepository
    {
        if ($this->isValidEntityManager()) {
            try {
                $className = $className ?: $this->getEntityName();
            } catch (Exception $e) {
                return null;
            }
            return $this->getEntityManager()->getRepository($className);
        }
        return null;
    }

    /**
     * @var bool|null
     */
    private $validEntityManager;

    /**
     * isValidEntityManager
     *
     * @return bool
     */
    public function isValidEntityManager(): bool
    {
        if (!is_null($this->validEntityManager))
            return $this->validEntityManager;
        return $this->validEntityManager = true;
    }

    /**
     * isValidEntity
     *
     * @return bool
     */
    public function isValidEntity(bool $entityOnly = false): bool
    {
        return $this->getEntity() instanceof $this->entityName && (intval($this->getEntity()->getId()) > 0 || $entityOnly);
    }

    /**
     * getAuthorizationChecker
     *
     * @return AuthorizationCheckerInterface
     */
    public function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
    }

    /**
     * getTranslator
     *
     * @return TranslatorInterface
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * setTranslator
     *
     * @param TranslatorInterface $translator
     * @return AbstractProvider
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    /**
     * getLogger
     * @return Logger
     * 10/06/2020 09:18
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return AbstractProvider
     */
    public function setLogger(Logger $logger): AbstractProvider
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * getRouter
     *
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * findOneBy
     * @param array $criteria
     * @return EntityInterface|null
     */
    public function findOneBy(array $criteria): ?EntityInterface
    {
        $this->entity = null;
        if ($this->getRepository() !== null)
            $this->entity = $this->getRepository()->findOneBy($criteria);
        return $this->entity;
    }

    /**
     * findBy
     * @param array $criteria
     * @param array|null $orderBy
     * @return array
     */
    public function findBy(array $criteria, ?array $orderBy = null): array
    {
        if ($this->getRepository() !== null)
            $results = $this->getRepository()->findBy($criteria, $orderBy);
        return $results;
    }

    /**
     * flush
     *
     * 16/08/2020 14:57
     * @return StatusManager
     */
    public function flush(): StatusManager
    {
        try {
            $this->getEntityManager()->flush();
            $this->getMessageManager()->success();
        } catch (Exception $e) {
            if ($this->env === 'dev') $this->getMessageManager()->setLogger($this->getLogger())->error($e->getMessage() . ' ' . get_class($e),[],false);
            $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
        }
        return $this->getMessageManager();
    }

    /**
     * findAsArray
     * @param EntityInterface|null $entity
     * @return array
     * @throws Exception
     */
    public function findAsArray(?EntityInterface $entity): array
    {
        if (empty($entity))
            return [];
        $className = get_class($entity);

        if (method_exists($entity, '__toArray'))
            return $entity->__toArray();

        $result = $this->getRepository($className)->createQueryBuilder('e')
            ->select('e')
            ->where('e.id = :id')
            ->setParameter('id', $entity->getId())
            ->getQuery()
            ->getArrayResult();
        return reset($result);
    }

    /**
     * @return ProviderFactory
     */
    public function getProviderFactory(): ProviderFactory
    {
        return $this->providerFactory;
    }

    /**
     * @var null|SessionInterface
     */
    private $session;

    /**
     * @return SessionInterface
     */
    public function getSession(): ?SessionInterface
    {
        if (null === $this->session)
            $this->session = $this->getRequest() ? $this->getRequest()->getSession() : null;

        return $this->session;
    }

    /**
     * @var Request|null
     */
    private $request;

    /**
     * @return SessionInterface
     */
    public function getRequest(): ?Request
    {
        if (null === $this->request)
            $this->request = $this->getStack()->getCurrentRequest();

        return $this->request;
    }

    /**
     * @return RequestStack
     */
    public function getStack(): RequestStack
    {
        return $this->stack ?: $this->stack = $this->getProviderFactory()::getStack();
    }

    /**
     * refresh
     * @param EntityInterface|null $entity
     * @return EntityInterface
     */
    public function refresh(?EntityInterface $entity = null): EntityInterface
    {
        if (null !== $entity && $this->entityName === get_class($entity)) {
            $this->getEntityManager()->refresh($entity);
            $this->setEntity($entity);
            return $this->getEntity();
        }
        if ($entity === null)
            $entity = $this->getEntity();
        $this->getEntityManager()->refresh($entity);
        return $entity;
    }

    /**
     * persistFlush
     *
     * 20/08/2020 15:44
     * @param EntityInterface $entity
     * @param bool $flush
     * @return StatusManager
     */
    public function persistFlush(EntityInterface $entity, bool $flush = true): StatusManager
    {
        if ($this->isStatusSuccess()) {
            try {
                $this->getEntityManager()->persist($entity);
                if ($flush) $this->flush();
            } catch (NotNullConstraintViolationException $e) {
                if ($this->env === 'dev') $this->getMessageManager()->setLogger($this->getLogger())->error($e->getMessage() . ' ' . get_class($e), [], false);
                $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
            } catch (UniqueConstraintViolationException $e) {
                if ($this->env === 'dev') $this->getMessageManager()->setLogger($this->getLogger())->error($e->getMessage() . ' ' . get_class($e), [], false);
                $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
            } catch (\PDOException | PDOException | ORMException $e) {
                if ($this->env === 'dev') $this->getMessageManager()->setLogger($this->getLogger())->error($e->getMessage() . ' ' . get_class($e), [], false);
                $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
            } catch (Exception $e) {
                if ($this->env === 'dev') $this->getMessageManager()->setLogger($this->getLogger())->error($e->getMessage() . ' ' . get_class($e), [], false);
                $this->getMessageManager()->error(StatusManager::DATABASE_ERROR);
            }
        }
        return $this->getMessageManager();
    }


    /**
     * persist
     * @param EntityInterface $entity
     * @param array $data
     * @return StatusManager 10/08/2020 08:56
     * 10/08/2020 08:56
     */
    public function persist(EntityInterface $entity, array $data = []): StatusManager
    {
        return $this->persistFlush($entity, $data, false);
    }

    /**
     * count
     * @return int
     */
    public function count(): int
    {
        return intval($this->getRepository()->createQueryBuilder('e')
            ->select("COUNT('id') as result")
            ->getQuery()
            ->getSingleScalarResult());
    }

    /**
     * @return ParameterBagInterface
     */
    public function getParameterBag(): ParameterBagInterface
    {
        return $this->parameterBag;
    }

    /**
     * findOneLike
     * @param array $criteria
     * @param array|null $orderBy
     * @return mixed
     * 22/07/2020 10:31
     * @throws NonUniqueResultException
     */
    public function findOneLike(array $criteria, ?array $orderBy = null)
    {
        return $this->createLikeQueryBuilder($criteria, $orderBy)->getQuery()->getOneOrNullResult();
    }

    /**
     * findOneLike
     * @param array $criteria
     * @param array|null $orderBy
     * @return mixed
     * 22/07/2020 10:31
     */
    public function findLike(array $criteria, ?array $orderBy = null)
    {
        return $this->createLikeQueryBuilder($criteria, $orderBy)->getQuery()->getResult();
    }

    /**
     * createLikeQueryBuilder
     * @param array $criteria
     * @param array|null $orderBy
     * @return QueryBuilder
     * 22/07/2020 10:44
     */
    private function createLikeQueryBuilder(array $criteria, ?array $orderBy = null): QueryBuilder
    {
        $query = $this->getRepository()->createQueryBuilder('x');
        $count = 0;
        foreach($criteria as $name => $value) {
            if ($count === 0) {
                $query->where('x.' . $name . ' LIKE :value'.$count);
            } else {
                $query->addWhere('x.' . $name . ' LIKE :value'.$count);
            }
            $query->setParameter('value'.$count, '%' . $value . '%');
            $count++;
        }
        if (is_array($orderBy)) {
            $count = 0;
            foreach($orderBy as $name=>$order) {
                if ($count === 0) {
                    $query->orderBy('x.' . $name, $order);
                } else {
                    $query->addOrderBy('x.' . $name, $order);
                }
                $count++;
            }
        }
        return $query;
    }

    /**
     * isStatusSuccess
     *
     * 16/08/2020 14:53
     * @return bool
     */
    public function isStatusSuccess(): bool
    {
        return $this->getMessageManager()->getStatus() === 'success' || $this->getMessageManager()->getStatus() === 'default';
    }
}
