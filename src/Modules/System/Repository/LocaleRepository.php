<?php
/**
 * Created by PhpStorm.
 *
 * Project: Kookaburra
 * Build: Quoll
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:58
 */
namespace App\Modules\System\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\DriverException;
use App\Modules\System\Entity\Locale;
use App\Modules\System\Util\LocaleHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocaleRepository
 * @package App\Modules\System\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class LocaleRepository extends ServiceEntityRepository
{
    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var ArrayCollection
     */
    private $localesByCode;

    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locale::class);
    }

    /**
     * findSystemDefaultCode
     * @return string|null
     */
    public function findSystemDefaultCode(): ?string
    {
        try {
            $systemDefault = $this->findOneBySystemDefault('Y');
        } catch (DriverException $e) {  //  Installation step over
            $systemDefault = null;
        }
        $this->addLocaleByCode($systemDefault);
        return $systemDefault ? $systemDefault->getCode() : null;
    }

    /**
     * findLocaleRightToLeft
     * @return bool
     * @throws \Exception
     */
    public function findLocaleRightToLeft(): bool
    {
        if (null === $this->locale)
            $this->locale = LocaleHelper::getLocale();

        $lang = $this->findOneByCode($this->locale);
        $this->addLocaleByCode($lang);
        return $lang ? $lang->isRtl() : false;
    }

    /**
     * findByActive
     * @return array
     */
    public function findByActive(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.active = :yes')
            ->andWhere('i.installed = :yes')
            ->orWhere('i.systemDefault = :yes')
            ->orderby('i.systemDefault', 'DESC')
            ->addOrderBy('i.name', 'ASC')
            ->setParameter('yes', 'Y')
            ->getQuery()
            ->getResult();
    }

    /**
     * findOneByCode
     * @param string $code
     * @param Request|null $request
     * @return Locale|null
     */
    public function findOneByCode(string $code, ?Request $request = null): ?Locale
    {
        if ($this->getLocalesByCode()->containsKey($code)) {
            return $this->getLocalesByCode()->get($code);
        }

        $locale = $this->findOneBy(['code' => $code]);
        if (null == $locale) {
            $locale = $this->findOneBy(['systemDefault' => 'Y']);
            if (null !== $request && null !== $locale) {
                $request->setDefaultLocale($locale->getCode());
                $request->setLocale($locale->getCode());
            }
        }

        $this->addLocaleByCode($locale);

        return $locale;
    }

    /**
     * getLocalesByCode
     * @return ArrayCollection
     * 11/06/2020 09:10
     */
    public function getLocalesByCode(): ArrayCollection
    {
        return $this->localesByCode = $this->localesByCode ?: new ArrayCollection();
    }

    /**
     * @param ArrayCollection $localesByCode
     * @return LocaleRepository
     */
    public function setLocalesByCode(ArrayCollection $localesByCode): LocaleRepository
    {
        $this->localesByCode = $localesByCode ?: new ArrayCollection();
        return $this;
    }

    /**
     * addLocaleByCode
     * @param Locale|null $locale
     * @return $this
     * 11/06/2020 09:12
     */
    public function addLocaleByCode(?Locale $locale): LocaleRepository
    {
        if ($locale !== null) {
            $this->getLocalesByCode()->set($locale->getCode(), $locale);
        }
        return $this;
    }
}
