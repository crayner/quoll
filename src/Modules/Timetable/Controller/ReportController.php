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
 * Date: 31/08/2020
 * Time: 08:44
 */
namespace App\Modules\Timetable\Controller;

use App\Controller\AbstractPageController;
use App\Modules\RollGroup\Entity\RollGroup;
use App\Modules\Timetable\Pagination\ClassEnrolmentByRollGroupPagination;
use App\Provider\ProviderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 * @package App\Modules\Timetable\Controller
 * @author Craig Rayner <craig@craigrayner.com>
 */
class ReportController extends AbstractPageController
{
    /**
     * classEnrolmentByRollGroup
     *
     * 31/08/2020 08:45
     * @param ClassEnrolmentByRollGroupPagination $pagination
     * @Route("/class/enrolment/by/roll/group/",name="class_enrolment_by_roll_group")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse
     */
    public function classEnrolmentByRollGroup(ClassEnrolmentByRollGroupPagination $pagination)
    {
        $pagination->setContent(ProviderFactory::getRepository(RollGroup::class)->findClassEnrolmentBy())
            ->setPageMax(50)
        ;

        return $this->getPageManager()
            ->createBreadcrumbs('Class Enrolment by Roll Group')
            ->render([
                'pagination' => $pagination->toArray(),
            ]);
    }
}
