<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
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

use Doctrine\DBAL\Exception\DriverException;
use App\Modules\System\Entity\I18n;
use App\Modules\System\Util\LocaleHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class I18nRepository
 * @package App\Modules\System\Repository
 */
class I18nRepository extends ServiceEntityRepository
{
    /**
     * @var string|null
     */
    private $locale;

    /**
     * ApplicationFormRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, I18n::class);
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
}
