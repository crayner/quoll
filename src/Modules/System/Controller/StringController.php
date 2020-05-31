<?php
/**
 * Created by PhpStorm.
 *
 * Quoll
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 24/04/2020
 * Time: 13:16
 */

namespace App\Modules\System\Controller;

use App\Container\ContainerManager;
use App\Controller\AbstractPageController;
use App\Modules\System\Entity\StringReplacement;
use App\Modules\System\Form\StringReplacementType;
use App\Modules\System\Pagination\StringReplacementPagination;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class StringController
 * @package App\Modules\System\Controller
 */
class StringController extends AbstractPageController
{
    /**
     * stringReplacementManage
     * @param StringReplacementPagination $pagination
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/string/replacement/manage/", name="string_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementManage(StringReplacementPagination $pagination)
    {
        $content = [];
        $provider = ProviderFactory::create(StringReplacement::class);
        $content = $provider->getPaginationResults();
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('string_add'));
        return $this->getPageManager()->createBreadcrumbs('String Replacements')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * stringReplacementEdit
     * @param ContainerManager $manager
     * @param string|null $stringReplacement
     * @Route("/string/replacement/{stringReplacement}/edit/", name="string_edit")
     * @Route("/string/replacement/add/", name="string_add")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse|Response
     */
    public function stringReplacementEdit(ContainerManager $manager, ?string $stringReplacement = 'Add')
    {
        $request = $this->getPageManager()->getRequest();

        $manager->setTranslationDomain('System');

        $stringReplacement = $stringReplacement !== 'Add' ? ProviderFactory::getRepository(StringReplacement::class)->find($stringReplacement) : new StringReplacement();
        $action = $stringReplacement->getId() !== null ? $this->generateUrl('string_edit', ['stringReplacement' => $stringReplacement->getId()]) : $this->generateUrl('string_add');

        $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $action]);

        if ($request->getContent() !== '') {
            $content = json_decode($request->getContent(), true);

            $data = [];
            $form->submit($content);
            if ($form->isValid()) {

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($stringReplacement);
                    $em->flush();
                    $data = ErrorMessageHelper::getSuccessMessage($data, true);
                    $action = $stringReplacement->getId() !== null ? $this->generateUrl('string_edit', ['stringReplacement' => $stringReplacement->getId()]) : $this->generateUrl('string_add');
                    $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $action]);
                } catch (PDOException $e) {
                    $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
                }
            } else {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return JsonResponse::create($data, 200);
        }

        $manager->setReturnRoute($this->generateUrl('string_manage'));
        if ($stringReplacement->getId() !== null)
            $manager->setAddElementRoute($this->generateUrl('string_add'));
        $manager->singlePanel($form->createView());

        return $this->getPageManager()->createBreadcrumbs($stringReplacement->getId() !== null ? 'Edit String' : 'Add String',
            [
                ['uri' => 'string_manage', 'name' => 'String Replacements'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * stringReplacementDelete
     * @param StringReplacement $stringReplacement
     * @param StringReplacementPagination $pagination
     * @return JsonResponse
     * @Route("/string/replacement/{stringReplacement}/delete/", name="string_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementDelete(StringReplacement $stringReplacement, StringReplacementPagination $pagination)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($stringReplacement);
            $em->flush();
            $this->getPageManager()->addMessage('success', ErrorMessageHelper::onlySuccessMessage(true));
        } catch (PDOException $e) {
            $this->getPageManager()->addMessage('error', ErrorMessageHelper::onlyDatabaseErrorMessage(true));
        }

        $content = ProviderFactory::create(StringReplacement::class)->getPaginationResults();
        $pagination->setContent($content)
            ->setAddElementRoute($this->generateUrl('string_add'));
        return $this->getPageManager()->createBreadcrumbs('String Replacements')
            ->render(['pagination' => $pagination->toArray()]);
    }
}