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
 * Date: 2/07/2020
 * Time: 11:39
 */
namespace App\Modules\People\Repository;

use App\Modules\People\Entity\PersonalDocumentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class PersonalDocumentationRepository
 * @package App\Modules\People\Repository
 * @author Craig Rayner <craig@craigrayner.com>
 */
class PersonalDocumentationRepository extends ServiceEntityRepository
{
    /**
     * PersonalDocumentationRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalDocumentation::class);
    }
}